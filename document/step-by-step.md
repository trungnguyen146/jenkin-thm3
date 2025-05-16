Dưới đây là nội dung hướng dẫn chi tiết bạn có thể copy trực tiếp, đầy đủ và rõ ràng để sử dụng hoặc lưu lại:

---

# Hướng dẫn cài đặt và thiết lập Jenkins CI/CD cho dự án Docker (có deploy SSH)

## Mục lục

1. Giới thiệu
2. Cài đặt Jenkins
3. Cấu hình Jenkins lần đầu
4. Cài đặt plugin cần thiết
5. Tạo credential cho Docker Hub và SSH
6. Tạo Jenkins pipeline CI/CD
7. Test pipeline
8. Một số lưu ý và mẹo hay

---

## 1. Giới thiệu

* Jenkins là công cụ tự động hóa tích hợp và triển khai (CI/CD) phổ biến, giúp tự động build, test và deploy dự án.
* Hướng dẫn này tập trung vào pipeline xây dựng image Docker, đẩy lên Docker Hub, và deploy lên VPS qua SSH.

---

## 2. Cài đặt Jenkins

* Tải Jenkins bản mới nhất tại [https://www.jenkins.io/download/](https://www.jenkins.io/download/)
* Cài đặt trên server Linux hoặc Windows theo hướng dẫn chính thức.
* Hoặc chạy Jenkins dưới dạng Docker container:

```bash
docker run -d -p 8080:8080 -p 50000:50000 --name jenkins \
  -v jenkins_home:/var/jenkins_home \
  jenkins/jenkins:lts
```

---

## 3. Cấu hình Jenkins lần đầu

* Mở trình duyệt truy cập `http://<jenkins-server-ip>:8080`
* Lấy mật khẩu admin lần đầu từ file:
  `/var/jenkins_home/secrets/initialAdminPassword` hoặc xem log container Docker
* Cài đặt plugin đề xuất hoặc chọn “Install suggested plugins”
* Tạo user admin đầu tiên

---

## 4. Cài đặt plugin cần thiết

Vào **Manage Jenkins > Manage Plugins**, tìm và cài:

* **Git plugin**
* **Docker Pipeline**
* **SSH Agent Plugin**
* **Credentials Binding Plugin**
* (Tuỳ chọn) **Mask Passwords Plugin**

Sau khi cài xong, khởi động lại Jenkins nếu cần.

---

## 5. Tạo credential cho Docker Hub và SSH

### 5.1. Credential Docker Hub

* Vào **Manage Jenkins > Credentials > System > Global credentials**
* Chọn **Add Credentials**
* Loại: **Username with password**
* Username: tài khoản Docker Hub
* Password: token hoặc mật khẩu Docker Hub
* ID đặt ví dụ: `github-pat`

### 5.2. Credential SSH

* Chuẩn bị private key SSH tương ứng với public key trên VPS
* Vào **Manage Jenkins > Credentials > System > Global credentials**
* Chọn **Add Credentials**
* Loại: **SSH Username with private key**
* Username: user SSH (ví dụ: root)
* Private Key: dán nội dung private key hoặc chọn file
* ID đặt ví dụ: `Prod_CredID`

---

## 6. Tạo Jenkins pipeline CI/CD

* Tạo project mới loại **Pipeline**
* Dán đoạn Jenkinsfile sau hoặc tham khảo dùng **Pipeline script from SCM**

```groovy
pipeline {
    agent {
        docker {
            image 'docker:dind'
            args '-v /var/run/docker.sock:/var/run/docker.sock --privileged'
        }
    }

    environment {
        IMAGE_NAME = 'trungnguyen146/php-website'
        IMAGE_TAG = 'ver1'
        FULL_IMAGE = "${IMAGE_NAME}:${IMAGE_TAG}"

        VPS_PRODUCTION_HOST = '14.225.219.14'
        CONTAINER_NAME_PRODUCTION = 'php-container-prod'
        HOST_PORT_PRODUCTION = 80
        APPLICATION_PORT = 80
        SSH_CREDENTIALS_ID = 'Prod_CredID'
    }

    triggers {
        pollSCM('H/2 * * * *')
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Build, Login & Push Image') {
            steps {
                withCredentials([usernamePassword(credentialsId: 'github-pat', usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                    sh """
                        echo "\$DOCKER_PASS" | docker login -u "\$DOCKER_USER" --password-stdin
                        docker buildx build -t ${FULL_IMAGE} -f Dockerfile . --push || {
                            docker build -t ${FULL_IMAGE} -f Dockerfile .
                            docker push ${FULL_IMAGE}
                        }
                        docker logout
                    """
                }
            }
        }

        stage('Deploy to Production') {
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
        always {
            echo '🧹 Cleaning up Docker system...'
            sh 'docker system prune -f'
        }
        success {
            echo '🎉 Pipeline finished successfully!'
        }
        failure {
            echo '💔 Pipeline failed. Check logs.'
        }
    }
}
```

---

## 7. Test pipeline

* Commit Jenkinsfile lên repo
* Jenkins tự động chạy theo trigger hoặc bạn chạy thủ công
* Kiểm tra log từng bước
* Khi đến bước deploy, Jenkins yêu cầu xác nhận

---

## 8. Lưu ý

* Public key SSH phải được thêm vào VPS đúng user
* Credential Jenkins phải đúng loại và chính xác
* Có thể dùng plugin Mask Passwords để ẩn mật khẩu trong log
* Nếu muốn ẩn bớt log, tách script ra file riêng gọi từ pipeline

---

Bạn có thể copy nguyên văn đoạn trên lưu lại hoặc chia sẻ.
Nếu cần mình tạo file PDF hoặc hướng dẫn thêm phần nào thì báo nhé!
