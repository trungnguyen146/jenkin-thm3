Dưới đây là hướng dẫn và ví dụ cụ thể cho 4 tính năng bạn yêu cầu tích hợp vào Jenkins pipeline:

---

## 1. **Version tag động**

Tạo tag image Docker theo số build hoặc commit git để dễ quản lý:

```groovy
environment {
    IMAGE_TAG = "ver${env.BUILD_NUMBER}"
    FULL_IMAGE = "${IMAGE_NAME}:${IMAGE_TAG}"
}
```

Hoặc lấy commit hash (ví dụ lấy 7 ký tự đầu):

```groovy
stage('Set Version') {
    steps {
        script {
            env.GIT_COMMIT_SHORT = sh(script: 'git rev-parse --short HEAD', returnStdout: true).trim()
            env.IMAGE_TAG = "git-${env.GIT_COMMIT_SHORT}"
            env.FULL_IMAGE = "${env.IMAGE_NAME}:${env.IMAGE_TAG}"
            echo "Image tag set to ${env.IMAGE_TAG}"
        }
    }
}
```

---

## 2. **Gửi thông báo khi build**

Ví dụ gửi email (cần cài plugin Email Extension):

```groovy
post {
    success {
        emailext (
            subject: "Build SUCCESS: ${currentBuild.fullDisplayName}",
            body: "Build succeeded. See details at ${env.BUILD_URL}",
            to: "team@company.com"
        )
    }
    failure {
        emailext (
            subject: "Build FAILURE: ${currentBuild.fullDisplayName}",
            body: "Build failed. See details at ${env.BUILD_URL}",
            to: "team@company.com"
        )
    }
}
```

Hoặc gửi Slack (cần cài plugin Slack Notification):

```groovy
post {
    success {
        slackSend(channel: '#ci-cd', color: 'good', message: "Build SUCCESS: ${env.JOB_NAME} #${env.BUILD_NUMBER}")
    }
    failure {
        slackSend(channel: '#ci-cd', color: 'danger', message: "Build FAILURE: ${env.JOB_NAME} #${env.BUILD_NUMBER}")
    }
}
```

---

## 3. **Hạn chế user trigger thủ công**

Bạn có thể dùng `properties` trong pipeline để giới hạn quyền trigger hoặc ẩn nút **Build with Parameters**:

```groovy
pipeline {
    options {
        disableConcurrentBuilds()  // Không cho chạy song song nhiều build
    }

    triggers {
        pollSCM('H/2 * * * *')
    }

    // Giới hạn quyền trigger thủ công:
    // 1. Sử dụng Role-Based Strategy plugin để phân quyền trong Jenkins UI  
    // 2. Trong pipeline, bạn có thể check user trigger và abort nếu không đúng, ví dụ:

    stages {
        stage('Check User') {
            steps {
                script {
                    def triggerUser = currentBuild.getBuildCauses('hudson.model.Cause$UserIdCause')[0]?.getUserId()
                    if (triggerUser != 'admin') {
                        error "Build chỉ được phép kích hoạt bởi admin, hiện tại: ${triggerUser}"
                    }
                }
            }
        }
        // Các stage khác ...
    }
}
```

---

## 4. **Ẩn thông tin nhạy cảm trong log - Mask Passwords Plugin**

* Cài plugin **Mask Passwords Plugin** trong Jenkins
* Khi dùng `withCredentials`, plugin tự động **ẩn giá trị password/token** trong log (thay bằng `****`), ví dụ:

```groovy
withCredentials([usernamePassword(credentialsId: 'github-pat', usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
    sh """
        echo "\$DOCKER_PASS" | docker login -u "\$DOCKER_USER" --password-stdin
        # các lệnh khác...
    """
}
```

Bạn không cần làm gì thêm, plugin sẽ tự động mask password khi hiển thị log.

---

## Tổng hợp ví dụ đoạn pipeline có tích hợp các tính năng trên

```groovy
pipeline {
    agent any

    environment {
        IMAGE_NAME = 'trungnguyen146/php-website'
        VPS_PRODUCTION_HOST = '14.225.219.14'
        CONTAINER_NAME_PRODUCTION = 'php-container-prod'
        HOST_PORT_PRODUCTION = 80
        APPLICATION_PORT = 80
        SSH_CREDENTIALS_ID = 'Prod_CredID'
    }

    options {
        disableConcurrentBuilds()
    }

    triggers {
        pollSCM('H/2 * * * *')
    }

    stages {
        stage('Set Version') {
            steps {
                script {
                    env.IMAGE_TAG = "ver${env.BUILD_NUMBER}"
                    env.FULL_IMAGE = "${env.IMAGE_NAME}:${env.IMAGE_TAG}"
                    echo "Image tag: ${env.IMAGE_TAG}"
                }
            }
        }

        stage('Check User') {
            steps {
                script {
                    def triggerUser = currentBuild.getBuildCauses('hudson.model.Cause$UserIdCause')[0]?.getUserId()
                    if (triggerUser != 'admin') {
                        error "Build chỉ được phép kích hoạt bởi admin, hiện tại: ${triggerUser}"
                    }
                }
            }
        }

        stage('Build & Push') {
            steps {
                withCredentials([usernamePassword(credentialsId: 'github-pat', usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                    sh """
                        echo "\$DOCKER_PASS" | docker login -u "\$DOCKER_USER" --password-stdin
                        docker buildx build -t ${env.FULL_IMAGE} -f Dockerfile . --push || {
                            docker build -t ${env.FULL_IMAGE} -f Dockerfile .
                            docker push ${env.FULL_IMAGE}
                        }
                        docker logout
                    """
                }
            }
        }

        stage('Deploy') {
            when {
                expression { currentBuild.currentResult == null || currentBuild.currentResult == 'SUCCESS' }
            }
            steps {
                input message: "Proceed with deployment to Production?"
                sshagent([env.SSH_CREDENTIALS_ID]) {
                    sh """
                        ssh -o StrictHostKeyChecking=no -T root@${env.VPS_PRODUCTION_HOST} '
                            docker pull ${env.FULL_IMAGE}
                            docker stop ${env.CONTAINER_NAME_PRODUCTION} || true
                            docker rm ${env.CONTAINER_NAME_PRODUCTION} || true
                            docker run -d --name ${env.CONTAINER_NAME_PRODUCTION} -p ${env.HOST_PORT_PRODUCTION}:${env.APPLICATION_PORT} ${env.FULL_IMAGE}
                            echo "✅ Deployed to Production"
                        '
                    """
                }
            }
        }
    }

    post {
        success {
            emailext(
                subject: "Build SUCCESS: ${currentBuild.fullDisplayName}",
                body: "Build succeeded. Xem chi tiết tại ${env.BUILD_URL}",
                to: "team@company.com"
            )
        }
        failure {
            emailext(
                subject: "Build FAILURE: ${currentBuild.fullDisplayName}",
                body: "Build thất bại. Xem chi tiết tại ${env.BUILD_URL}",
                to: "team@company.com"
            )
        }
    }
}
```

---

Nếu cần thêm hướng dẫn chi tiết về từng phần, bạn cứ hỏi nhé!
