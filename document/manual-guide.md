# Tài liệu Hướng dẫn Cài đặt CI/CD với Jenkins (Phiên bản Docker Compose)

**Mục tiêu:**
Hướng dẫn này nhằm mục đích giúp bạn thiết lập một quy trình CI/CD cơ bản sử dụng Jenkins để tự động hóa việc build, test (cơ bản), đóng gói Docker image, đẩy lên Docker Hub và triển khai một ứng dụng web đơn giản (ví dụ: PHP) lên một server production.

**Thời gian tham khảo:** Ngày 16 tháng 5 năm 2025

---

## Phần 1: Yêu Cầu Tiên Quyết (Điều chỉnh cho Docker Compose)

Trước khi bắt đầu, bạn cần chuẩn bị:

1.  **Server để chạy Jenkins (qua Docker Compose):**
    * Hệ điều hành: Linux (khuyến nghị Ubuntu 20.04/22.04 LTS hoặc CentOS/RHEL). Windows hoặc macOS có Docker Desktop cũng có thể hoạt động, nhưng hướng dẫn này tập trung vào Linux server.
    * **Đã cài đặt Docker và Docker Compose:** Đây là yêu cầu bắt buộc.
        * Hướng dẫn cài Docker: [https://docs.docker.com/engine/install/](https://docs.docker.com/engine/install/)
        * Hướng dẫn cài Docker Compose: [https://docs.docker.com/compose/install/](https://docs.docker.com/compose/install/)
    * Cấu hình tối thiểu cho server host: 2 vCPU, 4GB RAM (khuyến nghị 8GB+ nếu Jenkins chạy nhiều job phức tạp), 50GB HDD/SSD.
    * Có quyền `sudo` hoặc quyền chạy lệnh `docker` và `docker-compose`.
    * Truy cập Internet để tải Docker images (Jenkins, dind, etc.).
    * **Lưu ý:** Bạn không cần cài đặt Java trực tiếp trên server host này vì Jenkins sẽ chạy trong một Docker container đã có sẵn Java.

2.  **Server cho Môi trường Production (VPS Production):**
    * Hệ điều hành: Linux.
    * Đã cài đặt Docker.
    * Có thể truy cập SSH bằng key từ container Jenkins (hoặc từ Jenkins agent nếu bạn cấu hình agent riêng).
    * Truy cập Internet để pull Docker image.
    * (Tùy chọn) Server Staging: Chuẩn bị tương tự Production.

3.  **Tài khoản và Công cụ:**
    * **Tài khoản GitHub (hoặc GitLab/Bitbucket):** Để lưu trữ mã nguồn ứng dụng.
    * **Tài khoản Docker Hub:** Để lưu trữ Docker image sau khi build.
    * **Kiến thức cơ bản:** Về Git, câu lệnh Linux, Docker (Dockerfile, image, container, **Docker Compose**), và SSH.

4.  **Ứng dụng Mẫu:**
    * Một ứng dụng web đơn giản. Ví dụ, một file `index.php` với nội dung:
        ```php
        <?php
        echo "<h1>Hello World from Jenkins CI/CD!</h1>";
        echo "<p>Version: 1.0.1</p>"; // Thay đổi version để thấy cập nhật
        echo "<p>Hostname: " . gethostname() . "</p>";
        echo "<p>Deployed at: " . date('Y-m-d H:i:s') . "</p>";
        ?>
        ```
    * Một `Dockerfile` để đóng gói ứng dụng. Ví dụ cho PHP với Apache:
        ```dockerfile
        FROM php:8.2-apache # Sử dụng base image PHP 8.2 với Apache
        COPY . /var/www/html/ # Copy toàn bộ source code vào thư mục web root của Apache
        EXPOSE 80
        ```
        (Đặt file `index.php` và `Dockerfile` này trong cùng một thư mục gốc của project).

---

## Phần 2: Cài Đặt và Cấu Hình Jenkins (Sử dụng Docker Compose)

1.  **Chuẩn bị thư mục và file `docker-compose.yml`:**
    Trên server bạn dùng để chạy Jenkins, tạo một thư mục để lưu trữ cấu hình Docker Compose và dữ liệu Jenkins.
    ```bash
    mkdir jenkins-server-compose
    cd jenkins-server-compose
    nano docker-compose.yml
    ```
    Dán nội dung sau vào file `docker-compose.yml`:
    ```yaml
    version: '3.8'

    services:
      jenkins:
        image: jenkins/jenkins:lts-jdk17 
        container_name: jenkins-lts
        restart: unless-stopped 
        ports:
          - "8080:8080" 
          - "50000:50000" 
        volumes:
          - jenkins_data:/var/jenkins_home 
          - /var/run/docker.sock:/var/run/docker.sock # Tùy chọn, cho phép Jenkins container dùng Docker daemon của host
        environment:
          - TZ=Asia/Ho_Chi_Minh # Cấu hình múi giờ, thay bằng múi giờ của bạn
          # - JAVA_OPTS=-Xmx2048m -Xms512m # Ví dụ cấu hình memory cho Jenkins

    volumes:
      jenkins_data: {} 
    ```

2.  **Khởi chạy Jenkins bằng Docker Compose:**
    Trong thư mục `jenkins-server-compose`, chạy lệnh:
    ```bash
    docker-compose up -d
    ```
    Kiểm tra trạng thái container: `docker-compose ps` hoặc `docker ps`.
    Xem log khởi tạo của Jenkins: `docker-compose logs -f jenkins`.

3.  **Thiết lập Jenkins lần đầu (sau khi chạy bằng Docker Compose):**
    * Truy cập Jenkins qua trình duyệt: `http://<IP_SERVER_CHAY_DOCKER_COMPOSE>:8080`.
    * **Unlock Jenkins:** Lấy `initialAdminPassword` từ log (lệnh `docker-compose logs jenkins`) hoặc bằng lệnh:
        ```bash
        docker exec jenkins-lts cat /var/jenkins_home/secrets/initialAdminPassword
        ```
        Copy mật khẩu này và dán vào trình duyệt.
    * **Install suggested plugins:** Chọn "Install suggested plugins".
    * **Create First Admin User:** Tạo tài khoản admin của bạn.

4.  **Cài đặt các Plugins cần thiết:**
    Đi đến **Manage Jenkins > Plugins > Available plugins**, tìm và cài đặt:
    * `Pipeline` (thường có sẵn)
    * `Git plugin` (thường có sẵn)
    * `Docker Pipeline`
    * `Docker Commons Plugin`
    * `SSH Pipeline Steps` (cung cấp `sshCommand`, `sshScript`, etc.)
    * `Credentials Binding Plugin` (thường có sẵn)
    * (Tùy chọn) `Blue Ocean`

5.  **Cấu hình Global Tool Configuration (nếu cần):**
    Thường không cần cấu hình gì thêm ở đây khi Jenkins chạy bằng Docker và pipeline agent của bạn là `docker:dind`.

6.  **Cấu hình Credentials trong Jenkins:**
    Đi đến **Manage Jenkins > Credentials > System > Global credentials (unrestricted) > Add Credentials**. Tạo các credentials:

    * **a. GitHub Personal Access Token (PAT):**
        * **Kind:** `Secret text`
        * **ID:** `github-pat` (hoặc ID bạn chọn)
        * **Secret:** PAT của bạn.

    * **b. Docker Hub Credentials:**
        * **Kind:** `Username with password`
        * **ID:** `dockerhub-credentials` (hoặc ID bạn chọn)
        * **Username:** Username Docker Hub.
        * **Password:** Password Docker Hub (hoặc Access Token).

    * **c. SSH Private Key cho Server Production/Staging:**
        * Tạo cặp key SSH nếu chưa có:
            ```bash
            ssh-keygen -t rsa -b 4096 -C "jenkins_ci@yourdomain.com" -f ~/.ssh/jenkins_deploy_key
            ```
            (Không đặt passphrase nếu không muốn cấu hình thêm trong Jenkins).
        * Trong Jenkins, tạo credential:
            * **Kind:** `SSH Username with private key`
            * **ID:** `prod-ssh-key` (hoặc ID bạn chọn)
            * **Username:** User để SSH vào server (ví dụ: `root` hoặc `deploy_user`).
            * **Private Key:** Chọn "Enter directly", dán toàn bộ nội dung file private key `jenkins_deploy_key`.

---

## Phần 3: Chuẩn Bị Môi Trường Production/Staging và Source Code

1.  **Trên Server Production (và Staging nếu có):**
    * **Cài đặt Docker:**
        ```bash
        sudo apt-get update
        sudo apt-get install -y docker.io
        sudo systemctl start docker
        sudo systemctl enable docker
        # sudo usermod -aG docker $USER # Thêm user vào group docker (cần login lại)
        ```
    * **Cấu hình SSH Key-based Authentication:**
        * Lấy nội dung public key (`jenkins_deploy_key.pub`) đã tạo.
        * Đăng nhập vào server Production.
        * Thêm public key vào file `~/.ssh/authorized_keys` của user mà Jenkins sẽ dùng để SSH (ví dụ `root`):
            ```bash
            # Nếu user là root
            mkdir -p /root/.ssh
            chmod 700 /root/.ssh
            echo "PASTE_PUBLIC_KEY_CONTENT_HERE" >> /root/.ssh/authorized_keys
            chmod 600 /root/.ssh/authorized_keys
            chown -R root:root /root/.ssh 
            ```
        * **Kiểm tra kết nối SSH từ server Jenkins Master:**
            ```bash
            # Nếu key jenkins_deploy_key nằm trên Jenkins master
            # ssh -i /path/to/jenkins_deploy_key root@<IP_VPS_PRODUCTION> 'echo "Connection successful"'
            ```

2.  **Chuẩn bị Source Code và Repository GitHub:**
    * Tạo thư mục project, thêm `index.php` và `Dockerfile` (như ví dụ ở Phần 1).
    * Đẩy code lên GitHub:
        ```bash
        git init
        git add .
        git commit -m "Initial commit with PHP app and Dockerfile"
        git remote add origin [https://github.com/YOUR_USERNAME/my-php-app.git](https://github.com/YOUR_USERNAME/my-php-app.git) # THAY YOUR_USERNAME
        git branch -M main
        git push -u origin main
        ```

---

## Phần 4: Tạo Jenkins Pipeline (Jenkinsfile)

1.  Trong thư mục gốc của project, tạo file `Jenkinsfile`:

    ```groovy
    // Jenkinsfile
    pipeline {
        agent {
            docker {
                image 'docker:dind'
                args '--privileged' 
            }
        }

        environment {
            // Credentials IDs - Phải khớp với ID bạn tạo trong Jenkins
            DOCKERHUB_CREDENTIALS_ID = 'dockerhub-credentials' 
            SSH_CREDENTIALS_ID = 'prod-ssh-key'             

            // Thông tin Docker Image - THAY THẾ BẰNG THÔNG TIN CỦA BẠN
            DOCKER_REGISTRY_USER = 'your_dockerhub_username' // Username Docker Hub của bạn
            APP_NAME = 'my-php-app' // Tên ứng dụng (hoặc tên image)
            IMAGE_TAG = "ver${env.BUILD_NUMBER}" 
            DOCKER_IMAGE_NAME = "${DOCKER_REGISTRY_USER}/${APP_NAME}" // Tên đầy đủ: username/app_name

            // Thông tin Server Production - THAY THẾ BẰNG THÔNG TIN CỦA BẠN
            PROD_SERVER_HOST = 'YOUR_PRODUCTION_VPS_IP' 
            PROD_CONTAINER_NAME = "${APP_NAME}-prod" // Tên container trên production
            PROD_HOST_PORT = 8081 // Port trên VPS map vào port ứng dụng
            APP_CONTAINER_PORT = 80 // Port ứng dụng chạy trong container

            // (Tùy chọn) Thông tin Server Staging
            // STAGING_SERVER_HOST = 'YOUR_STAGING_VPS_IP'
            // STAGING_CONTAINER_NAME = "${APP_NAME}-staging"
            // STAGING_HOST_PORT = 8080
        }

        triggers {
            // pollSCM('H/5 * * * *') 
        }

        stages {
            stage('1. Checkout SCM') {
                steps {
                    echo "Checking out source code..."
                    checkout scm
                }
            }

            stage('2. Login to Docker Hub') {
                steps {
                    script {
                        withCredentials([usernamePassword(credentialsId: env.DOCKERHUB_CREDENTIALS_ID, usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                            echo "Logging in to Docker Hub as ${DOCKER_USER}..."
                            sh "echo \"${DOCKER_PASS}\" | docker login -u \"${DOCKER_USER}\" --password-stdin"
                        }
                    }
                }
            }

            stage('3. Build and Push Docker Image') {
                steps {
                    script {
                        def fullImageNameWithTag = "${env.DOCKER_IMAGE_NAME}:${env.IMAGE_TAG}"
                        echo "Building Docker image: ${fullImageNameWithTag}..."
                        sh "docker build -t ${fullImageNameWithTag} ."
                        
                        echo "Pushing Docker image: ${fullImageNameWithTag} to Docker Hub..."
                        sh "docker push ${fullImageNameWithTag}"
                        
                        echo "Image pushed: ${fullImageNameWithTag}"
                    }
                }
            }

            // (Tùy chọn) Stage Deploy to Staging
            /*
            stage('4. Deploy to Staging') {
                when { expression { return currentBuild.result == null || currentBuild.result == 'SUCCESS' } }
                steps {
                    script {
                        def remoteStagingConfig = [:]
                        remoteStagingConfig.name = "staging-server"
                        remoteStagingConfig.host = env.STAGING_SERVER_HOST
                        remoteStagingConfig.allowAnyHosts = true // Hoặc knownHosts: 'NONE'
                        
                        withCredentials([sshUserPrivateKey(
                            credentialsId: env.SSH_CREDENTIALS_ID,
                            keyFileVariable: 'stagingKeyFile',
                            usernameVariable: 'stagingSshUser'
                        )]) {
                            remoteStagingConfig.user = stagingSshUser
                            remoteStagingConfig.identityFile = stagingKeyFile

                            echo "Deploying ${env.DOCKER_IMAGE_NAME}:${env.IMAGE_TAG} to Staging server ${remoteStagingConfig.host}..."
                            def deployScriptStaging = """
                                echo 'Pulling image on Staging...'
                                docker pull ${env.DOCKER_IMAGE_NAME}:${env.IMAGE_TAG} || exit 1
                                echo 'Stopping old container on Staging (if any)...'
                                docker stop ${env.STAGING_CONTAINER_NAME} || true
                                echo 'Removing old container on Staging (if any)...'
                                docker rm ${env.STAGING_CONTAINER_NAME} || true
                                echo 'Running new container on Staging...'
                                docker run -d --name ${env.STAGING_CONTAINER_NAME} -p ${env.STAGING_HOST_PORT}:${env.APP_CONTAINER_PORT} ${env.DOCKER_IMAGE_NAME}:${env.IMAGE_TAG}
                                echo 'Deployment to Staging completed!'
                            """
                            sshCommand remote: remoteStagingConfig, command: deployScriptStaging
                        }
                    }
                }
            }
            */

            stage('5. Deploy to Production') {
                when { expression { return currentBuild.result == null || currentBuild.result == 'SUCCESS' } }
                steps {
                    input message: "Proceed with deployment of ${env.DOCKER_IMAGE_NAME}:${env.IMAGE_TAG} to Production?", submitter: 'admins' // Giả sử có group 'admins', hoặc bỏ submitter

                    script {
                        def remoteProdConfig = [:]
                        remoteProdConfig.name = "production-server"
                        remoteProdConfig.host = env.PROD_SERVER_HOST
                        remoteProdConfig.allowAnyHosts = true // Hoặc knownHosts: 'NONE'
                        
                        withCredentials([sshUserPrivateKey(
                            credentialsId: env.SSH_CREDENTIALS_ID,
                            keyFileVariable: 'prodKeyFile',
                            usernameVariable: 'prodSshUser'
                        )]) {
                            remoteProdConfig.user = prodSshUser
                            remoteProdConfig.identityFile = prodKeyFile

                            echo "Deploying ${env.DOCKER_IMAGE_NAME}:${env.IMAGE_TAG} to Production server ${remoteProdConfig.host}..."
                            def deployScriptProd = """
                                echo 'Pulling image ${env.DOCKER_IMAGE_NAME}:${env.IMAGE_TAG} on Production server...'
                                docker pull ${env.DOCKER_IMAGE_NAME}:${env.IMAGE_TAG} || exit 1
                                echo 'Stopping old container ${env.PROD_CONTAINER_NAME} on Production (if any)...'
                                docker stop ${env.PROD_CONTAINER_NAME} || true
                                echo 'Removing old container ${env.PROD_CONTAINER_NAME} on Production (if any)...'
                                docker rm ${env.PROD_CONTAINER_NAME} || true
                                echo 'Running new container ${env.PROD_CONTAINER_NAME} on Production...'
                                docker run -d --name ${env.PROD_CONTAINER_NAME} -p ${env.PROD_HOST_PORT}:${env.APP_CONTAINER_PORT} ${env.DOCKER_IMAGE_NAME}:${env.IMAGE_TAG}
                                echo 'Deployment to Production completed!'
                            """
                            sshCommand remote: remoteProdConfig, command: deployScriptProd
                        }
                    }
                }
            }
        }

        post {
            always {
                echo 'Pipeline finished. Cleaning up workspace and Docker on agent...'
                cleanWs() 
                sh 'docker system prune -af || true' // Thêm || true để không làm fail pipeline nếu prune lỗi
            }
            success {
                echo '🎉 CI/CD Pipeline finished successfully!'
            }
            failure {
                echo '💔 CI/CD Pipeline failed. Please check logs.'
            }
        }
    }
    ```

2.  **Đẩy `Jenkinsfile` lên Repository GitHub.**

---

## Phần 5: Tạo và Chạy Jenkins Pipeline Job

1.  **Tạo Job mới trong Jenkins:**
    * Dashboard Jenkins > **New Item**.
    * Tên item: `my-php-app-pipeline`.
    * Chọn **Pipeline** > **OK**.

2.  **Cấu hình Pipeline Job:**
    * **Pipeline section:**
        * **Definition:** `Pipeline script from SCM`.
        * **SCM:** `Git`.
        * **Repository URL:** URL repo GitHub của bạn.
        * **Credentials:** Chọn GitHub PAT nếu repo private.
        * **Branch Specifier:** `*/main`.
        * **Script Path:** `Jenkinsfile`.
    * **Save**.

3.  **Chạy Pipeline:** Nhấn **Build Now**. Theo dõi trong **Build History** và **Console Output**.

---

## Phần 6: Giải Thích Sơ Lược về Jenkinsfile

* **`pipeline { ... }`**: Khối chính.
* **`agent { docker { ... } }`**: Môi trường thực thi (dùng Docker-in-Docker).
* **`environment { ... }`**: Biến môi trường. **NHỚ THAY THẾ CÁC GIÁ TRỊ PLACEHOLDER.**
* **`triggers { ... }`**: Kích hoạt tự động.
* **`stages { ... }`**: Các giai đoạn logic.
    * **`withCredentials([...]) { ... }`**: Dùng credentials an toàn.
    * **`sh "..."`**: Thực thi lệnh shell.
    * **`sshCommand remote: ..., command: ...`**: Thực thi lệnh qua SSH.
    * **`input message: ...`**: Chờ xác nhận thủ công.
* **`post { ... }`**: Hành động sau khi pipeline hoàn thành.
    * **`cleanWs()`**: Dọn dẹp workspace.

---

## Phần 7: Tùy Chỉnh và Mở Rộng

* **Testing:** Thêm stage chạy unit test, integration test.
* **Multiple Environments:** Tạo stage deploy riêng cho Staging, UAT.
* **Notifications:** Tích hợp gửi thông báo qua Email, Slack.
* **Security Scanning:** Tích hợp công cụ quét lỗ hổng.
* **Rollback Strategies:** Xây dựng cơ chế rollback.
* **Dynamic Tagging:** Dùng Git commit hash cho `IMAGE_TAG`.
    ```groovy
    // Ví dụ: IMAGE_TAG = sh(script: 'git rev-parse --short HEAD', returnStdout: true).trim()
    ```
* **Docker Hub Access Token:** Dùng Access Token thay vì password cho Docker Hub.

---

## Phần 8: Gỡ Lỗi (Troubleshooting) Phổ Biến

* **Permission Denied (SSH):**
    * Public key từ Jenkins credential (`prod-ssh-key`) phải được thêm **chính xác** vào `~/.ssh/authorized_keys` trên server đích cho đúng user.
    * Quyền thư mục `~/.ssh` (`700`) và file `authorized_keys` (`600`) trên server đích.
    * Log `sshd` trên server đích (`/var/log/auth.log`, `/var/log/secure`, `journalctl -u sshd -f`).
    * `Username` trong SSH credential của Jenkins khớp với user trên server đích.

* **Docker Login Failed:**
    * Kiểm tra Docker Hub username/password (hoặc token) trong Jenkins credential.
    * ID credential trong Jenkinsfile (`env.DOCKERHUB_CREDENTIALS_ID`) khớp ID đã tạo.

* **Docker Build Failed:**
    * Lỗi trong `Dockerfile`.
    * Log build chi tiết trong Console Output của Jenkins.

* **`sshCommand` Not Found (`NoSuchMethodError`):**
    * Plugin "SSH Pipeline Steps" phải được cài đặt và kích hoạt.

* **`allowAnyHosts = true` không hoạt động:**
    * Thử thay bằng `knownHosts: 'NONE'` trong map `remote` của `sshCommand`.

---

## Kết luận
Thiết lập CI/CD là một quá trình đầu tư ban đầu nhưng mang lại lợi ích lớn. Hướng dẫn này cung cấp nền tảng cơ bản. Hãy tùy chỉnh để phù hợp với dự án của bạn.

Chúc bạn thành công!
