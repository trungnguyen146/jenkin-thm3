# Tài liệu Hướng dẫn Cài đặt CI/CD với Jenkins

**Mục tiêu:**
Hướng dẫn này nhằm mục đích giúp bạn thiết lập một quy trình CI/CD cơ bản sử dụng Jenkins để tự động hóa việc build, test (cơ bản), đóng gói Docker image, đẩy lên Docker Hub và triển khai một ứng dụng web đơn giản (ví dụ: PHP) lên một server production.

**Thời gian tham khảo:** Ngày 16 tháng 5 năm 2025

---

## Phần 1: Yêu Cầu Tiên Quyết

Trước khi bắt đầu, bạn cần chuẩn bị:

1.  **Server cho Jenkins Master:**
    * Hệ điều hành: Linux (khuyến nghị Ubuntu 20.04/22.04 LTS hoặc CentOS/RHEL).
    * Cấu hình tối thiểu: 2 vCPU, 4GB RAM, 50GB HDD/SSD (SSD tốt hơn).
    * Đã cài đặt Java (JDK 11 hoặc JDK 17 là các phiên bản được Jenkins LTS hỗ trợ tốt hiện nay - kiểm tra trang chủ Jenkins để biết phiên bản Java khuyến nghị mới nhất).
    * Có quyền `sudo` hoặc `root`.
    * Truy cập Internet để tải Jenkins và các plugin.

2.  **Server cho Môi trường Production (VPS Production):**
    * Hệ điều hành: Linux.
    * Đã cài đặt Docker.
    * Có thể truy cập SSH bằng key từ Jenkins Master.
    * Truy cập Internet để pull Docker image.
    * (Tùy chọn) Server Staging: Nếu bạn muốn có môi trường Staging, chuẩn bị tương tự Production.

3.  **Tài khoản và Công cụ:**
    * **Tài khoản GitHub (hoặc GitLab/Bitbucket):** Để lưu trữ mã nguồn ứng dụng.
    * **Tài khoản Docker Hub:** Để lưu trữ Docker image sau khi build.
    * **Docker:** Cần cài đặt Docker trên máy bạn dùng để tạo Dockerfile (nếu bạn phát triển local) và trên server Production/Staging.
    * **Kiến thức cơ bản:** Về Git, câu lệnh Linux, Docker (Dockerfile, image, container), và SSH.

4.  **Ứng dụng Mẫu:**
    * Một ứng dụng web đơn giản. Ví dụ, một file `index.php` với nội dung:
        ```php
        <?php
        echo "<h1>Hello World from Jenkins CI/CD!</h1>";
        echo "<p>Version: 1.0.0</p>";
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

## Phần 2: Cài Đặt và Cấu Hình Jenkins

1.  **Cài đặt Java (nếu chưa có):**
    Kiểm tra phiên bản Java được Jenkins khuyến nghị tại [https://www.jenkins.io/doc/administration/requirements/java/](https://www.jenkins.io/doc/administration/requirements/java/).
    Ví dụ cài đặt OpenJDK 17 trên Ubuntu:
    ```bash
    sudo apt update
    sudo apt install -y openjdk-17-jdk
    java -version # Kiểm tra phiên bản
    ```

2.  **Cài đặt Jenkins:**
    Làm theo hướng dẫn chính thức trên trang chủ Jenkins cho hệ điều hành của bạn: [https://www.jenkins.io/doc/book/installing/](https://www.jenkins.io/doc/book/installing/)
    Ví dụ cho Ubuntu (sử dụng LTS package):
    ```bash
    # Thêm GPG key
    sudo wget -O /usr/share/keyrings/jenkins-keyring.asc \
      [https://pkg.jenkins.io/debian-lts/jenkins.io-2023.key](https://pkg.jenkins.io/debian-lts/jenkins.io-2023.key)
    # Thêm Jenkins repository
    echo "deb [signed-by=/usr/share/keyrings/jenkins-keyring.asc]" \
      [https://pkg.jenkins.io/debian-lts](https://pkg.jenkins.io/debian-lts) binary/ | sudo tee \
      /etc/apt/sources.list.d/jenkins.list > /dev/null
    # Cài đặt Jenkins
    sudo apt-get update
    sudo apt-get install -y jenkins
    # Khởi động và kiểm tra status
    sudo systemctl start jenkins
    sudo systemctl enable jenkins # Tự khởi động cùng hệ thống
    sudo systemctl status jenkins
    ```
    Jenkins thường chạy trên port `8080`. Truy cập `http://<IP_SERVER_JENKINS>:8080`.

3.  **Thiết lập Jenkins lần đầu:**
    * **Unlock Jenkins:** Lấy initial admin password từ file trên server Jenkins:
        ```bash
        sudo cat /var/lib/jenkins/secrets/initialAdminPassword
        ```
        Copy password này và dán vào trình duyệt.
    * **Install suggested plugins:** Chọn "Install suggested plugins". Quá trình này có thể mất vài phút.
    * **Create First Admin User:** Tạo tài khoản admin của bạn.

4.  **Cài đặt các Plugins cần thiết:**
    Sau khi vào được dashboard Jenkins, đi đến **Manage Jenkins > Plugins** (hoặc Manage Plugins).
    Trong tab **Available plugins**, tìm và cài đặt các plugin sau:
    * `Pipeline`: Thường đã được cài sẵn, đây là plugin cốt lõi cho Jenkinsfile.
    * `Git plugin`: Để checkout code từ Git. Thường đã được cài.
    * `Docker Pipeline`: Cung cấp các bước tích hợp Docker vào pipeline (ví dụ: `docker.build()`, `image.push()`).
    * `Docker Commons Plugin`: Thường là dependency của Docker Pipeline.
    * `SSH Pipeline Steps`: Cung cấp các bước tiện lợi để thực thi lệnh qua SSH (`sshCommand`, `sshScript`, `sshPut`, `sshGet`).
    * `Credentials Binding Plugin`: Để sử dụng credentials một cách an toàn trong pipeline. Thường đã được cài.
    * (Tùy chọn) `Blue Ocean`: Cung cấp giao diện người dùng hiện đại hơn cho pipeline.

    Chọn các plugin và nhấn "Install without restart" (hoặc "Download now and install after restart").

5.  **Cấu hình Global Tool Configuration (nếu cần):**
    * **Manage Jenkins > Tools** (hoặc Global Tool Configuration).
    * **JDK:** Nếu bạn cài nhiều JDK, có thể cấu hình ở đây. Jenkins thường tự phát hiện JDK đã cài.
    * **Git:** Thường Jenkins tự phát hiện Git. Nếu không, bạn cần chỉ đường dẫn.
    * **Docker:** Nếu Jenkins master cũng là nơi build Docker image (và không dùng agent Docker-in-Docker), bạn có thể cần cấu hình Docker tool. Tuy nhiên, với agent `docker:dind` trong Jenkinsfile, Jenkins master không cần cài Docker trực tiếp.

6.  **Cấu hình Credentials trong Jenkins:**
    Đây là bước cực kỳ quan trọng để Jenkins có thể tương tác với các dịch vụ khác một cách an toàn.
    Đi đến **Manage Jenkins > Credentials > System > Global credentials (unrestricted)** (hoặc một domain cụ thể nếu bạn muốn). Nhấn **Add Credentials**.

    * **a. GitHub Personal Access Token (PAT):**
        * **Kind:** `Secret text`
        * **Secret:** Dán GitHub PAT của bạn vào đây. (Tạo PAT trên GitHub với quyền `repo` để đọc repository).
        * **ID:** `github-pat` (hoặc một ID dễ nhớ, bạn sẽ dùng ID này trong Jenkinsfile).
        * **Description:** (Tùy chọn) Mô tả.

    * **b. Docker Hub Credentials:**
        * **Kind:** `Username with password`
        * **Username:** Username Docker Hub của bạn.
        * **Password:** Password Docker Hub của bạn (hoặc Access Token nếu Docker Hub hỗ trợ).
        * **ID:** `dockerhub-credentials` (ví dụ, bạn sẽ dùng ID này).
        * **Description:** (Tùy chọn) Mô tả.

    * **c. SSH Private Key cho Server Production/Staging:**
        * **Tạo cặp SSH Key:** Nếu bạn chưa có, hãy tạo một cặp key SSH mới (ví dụ: trên Jenkins master hoặc máy local của bạn):
            ```bash
            ssh-keygen -t rsa -b 4096 -C "jenkins_ci@yourdomain.com" -f ~/.ssh/jenkins_deploy_key
            # Không đặt passphrase cho key này nếu bạn không muốn cấu hình passphrase trong Jenkins.
            ```
            Bạn sẽ có `jenkins_deploy_key` (private key) và `jenkins_deploy_key.pub` (public key).
        * **Cấu hình Credential trong Jenkins:**
            * **Kind:** `SSH Username with private key`
            * **ID:** `prod-ssh-key` (ví dụ, bạn sẽ dùng ID này).
            * **Description:** (Tùy chọn) Ví dụ: "SSH Key for Production VPS".
            * **Username:** User bạn sẽ dùng để SSH vào server Production (ví dụ: `root` hoặc một user riêng cho Jenkins).
            * **Private Key:** Chọn "Enter directly". Copy **toàn bộ** nội dung của file private key `jenkins_deploy_key` (bao gồm `-----BEGIN RSA PRIVATE KEY-----` và `-----END RSA PRIVATE KEY-----`) và dán vào ô "Key".
            * **Passphrase:** Để trống nếu key của bạn không có passphrase.

---

## Phần 3: Chuẩn Bị Môi Trường Production/Staging và Source Code

1.  **Trên Server Production (và Staging nếu có):**
    * **Cài đặt Docker:** Nếu chưa có, hãy cài Docker theo hướng dẫn cho HĐH của server.
        ```bash
        # Ví dụ cho Ubuntu
        sudo apt-get update
        sudo apt-get install -y docker.io
        sudo systemctl start docker
        sudo systemctl enable docker
        # Thêm user hiện tại (hoặc user Jenkins sẽ dùng) vào group docker để không cần sudo khi chạy lệnh docker
        # sudo usermod -aG docker $USER 
        # newgrp docker # Cần logout/login lại hoặc chạy lệnh này để có hiệu lực
        ```
    * **Cấu hình SSH Key-based Authentication:**
        * Lấy nội dung public key `jenkins_deploy_key.pub` mà bạn đã tạo ở Phần 2, Mục 6c.
        * Đăng nhập vào server Production.
        * Thêm public key này vào file `~/.ssh/authorized_keys` của user mà Jenkins sẽ dùng để SSH vào (ví dụ, user `root` hoặc user `jenkins_deploy` nếu bạn tạo riêng):
            ```bash
            # Nếu user là root
            mkdir -p /root/.ssh
            chmod 700 /root/.ssh
            echo "PASTE_PUBLIC_KEY_CONTENT_HERE" >> /root/.ssh/authorized_keys
            chmod 600 /root/.ssh/authorized_keys
            chown -R root:root /root/.ssh # Đảm bảo ownership
            ```
            Nếu dùng user khác, thay `/root/` bằng `/home/your_deploy_user/`.
        * **Kiểm tra kết nối SSH từ Jenkins Master (khuyến nghị):**
            Trên server Jenkins Master, thử SSH tới server Production bằng private key và user đã cấu hình để đảm bảo key hoạt động trước khi chạy pipeline.
            ```bash
            # Trên Jenkins Master, nếu bạn lưu private key ở ~/.ssh/jenkins_deploy_key
            ssh -i ~/.ssh/jenkins_deploy_key root@<IP_VPS_PRODUCTION> 'echo "Connection successful"'
            ```

2.  **Chuẩn bị Source Code và Repository GitHub:**
    * Tạo một thư mục cho project của bạn.
    * Bên trong thư mục đó, tạo file `index.php` và `Dockerfile` như ví dụ ở Phần 1, Mục 4.
    * Khởi tạo Git repository, commit code và đẩy lên GitHub:
        ```bash
        git init
        git add .
        git commit -m "Initial commit with PHP app and Dockerfile"
        # Tạo repository mới trên GitHub (ví dụ: my-php-app)
        git remote add origin [https://github.com/YOUR_USERNAME/my-php-app.git](https://github.com/YOUR_USERNAME/my-php-app.git) # THAY YOUR_USERNAME
        git branch -M main
        git push -u origin main
        ```

---

## Phần 4: Tạo Jenkins Pipeline (Jenkinsfile)

1.  **Trong thư mục gốc của project (nơi có `index.php` và `Dockerfile`), tạo một file mới tên là `Jenkinsfile`** (không có phần mở rộng).
    Đây là nội dung mẫu cho `Jenkinsfile` của bạn:

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
            // Credentials IDs đã cấu hình trong Jenkins
            DOCKERHUB_CREDENTIALS_ID = 'dockerhub-credentials' 
            SSH_CREDENTIALS_ID = 'prod-ssh-key'             

            // Thông tin Docker Image - THAY THẾ BẰNG THÔNG TIN CỦA BẠN
            DOCKER_IMAGE_NAME = 'your_dockerhub_username/my-php-app' 
            IMAGE_TAG = "ver${env.BUILD_NUMBER}" 

            // Thông tin Server Production - THAY THẾ BẰNG THÔNG TIN CỦA BẠN
            PROD_SERVER_HOST = 'YOUR_PRODUCTION_VPS_IP' 
            PROD_CONTAINER_NAME = 'my-php-app-prod'
            PROD_HOST_PORT = 8081 
            APP_CONTAINER_PORT = 80 

            // (Tùy chọn) Thông tin Server Staging
            // STAGING_SERVER_HOST = 'YOUR_STAGING_VPS_IP'
            // STAGING_CONTAINER_NAME = 'my-php-app-staging'
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
                        def fullImageName = "${env.DOCKER_IMAGE_NAME}:${env.IMAGE_TAG}"
                        echo "Building Docker image: ${fullImageName}..."
                        sh "docker build -t ${fullImageName} ."
                        
                        echo "Pushing Docker image: ${fullImageName} to Docker Hub..."
                        sh "docker push ${fullImageName}"
                        
                        echo "Image pushed: ${fullImageName}"
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
                    input message: "Proceed with deployment of ${env.DOCKER_IMAGE_NAME}:${env.IMAGE_TAG} to Production?", submitter: 'admins'

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
                                echo 'Pulling image on Production server...'
                                docker pull ${env.DOCKER_IMAGE_NAME}:${env.IMAGE_TAG} || exit 1
                                echo 'Stopping old container on Production (if any)...'
                                docker stop ${env.PROD_CONTAINER_NAME} || true
                                echo 'Removing old container on Production (if any)...'
                                docker rm ${env.PROD_CONTAINER_NAME} || true
                                echo 'Running new container on Production...'
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
                sh 'docker system prune -af || true'
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

2.  **Đẩy `Jenkinsfile` lên Repository GitHub:**
    ```bash
    git add Jenkinsfile
    git commit -m "Add Jenkinsfile for CI/CD pipeline"
    git push origin main
    ```

---

## Phần 5: Tạo và Chạy Jenkins Pipeline Job

1.  **Tạo Job mới trong Jenkins:**
    * Trên Dashboard Jenkins, nhấn **New Item**.
    * Đặt tên cho item (ví dụ: `my-php-app-pipeline`).
    * Chọn **Pipeline**.
    * Nhấn **OK**.

2.  **Cấu hình Pipeline Job:**
    * **Description:** (Tùy chọn) Mô tả pipeline của bạn.
    * **Pipeline section:**
        * **Definition:** Chọn `Pipeline script from SCM`.
        * **SCM:** Chọn `Git`.
        * **Repository URL:** Dán URL của repository GitHub của bạn (ví dụ: `https://github.com/YOUR_USERNAME/my-php-app.git`).
        * **Credentials:** Chọn credential GitHub PAT (`github-pat`) nếu repository của bạn là private. Nếu public, có thể để `none`.
        * **Branch Specifier:** Mặc định là `*/main` hoặc `*/master`. Để `*/main` nếu nhánh chính của bạn là `main`.
        * **Script Path:** Mặc định là `Jenkinsfile`. Giữ nguyên vì bạn đã đặt tên file là `Jenkinsfile`.
    * Nhấn **Save**.

3.  **Chạy Pipeline:**
    * Sau khi lưu, bạn sẽ thấy trang của Job. Nhấn **Build Now** ở menu bên trái để chạy pipeline lần đầu tiên.
    * Theo dõi quá trình chạy trong **Build History** và **Console Output** của build đó.

---

## Phần 6: Giải Thích Sơ Lược về Jenkinsfile

* **`pipeline { ... }`**: Khối chính định nghĩa toàn bộ pipeline.
* **`agent { docker { ... } }`**: Chỉ định môi trường thực thi pipeline. Ở đây dùng Docker-in-Docker (dind) để có môi trường Docker sạch cho mỗi lần build.
* **`environment { ... }`**: Định nghĩa các biến môi trường sẽ được sử dụng trong pipeline. **Hãy nhớ thay thế các giá trị placeholder (ví dụ: `YOUR_PRODUCTION_VPS_IP`, `your_dockerhub_username/my-php-app`) bằng thông tin thực tế của bạn.**
* **`triggers { ... }`**: (Tùy chọn) Định nghĩa cách pipeline được kích hoạt tự động.
* **`stages { ... }`**: Chia pipeline thành các giai đoạn logic.
    * **`stage('...') { steps { ... } }`**: Mỗi stage có các bước thực thi.
    * **`script { ... }`**: Cho phép viết mã Groovy phức tạp hơn bên trong steps.
    * **`withCredentials([...]) { ... }`**: Truy cập các credentials đã lưu trong Jenkins một cách an toàn.
    * **`sh "..."`**: Thực thi lệnh shell.
    * **`sshCommand remote: ..., command: ...`**: Thực thi lệnh trên server từ xa thông qua SSH.
    * **`input message: ...`**: Tạm dừng pipeline để chờ xác nhận thủ công.
* **`post { ... }`**: Các hành động được thực hiện sau khi pipeline hoàn thành.
    * **`cleanWs()`**: Dọn dẹp workspace của Jenkins job.

---

## Phần 7: Tùy Chỉnh và Mở Rộng

* **Testing:** Thêm các stage để chạy unit test, integration test.
* **Multiple Environments:** Tạo các stage deploy riêng cho Staging, UAT.
* **Notifications:** Tích hợp gửi thông báo qua Email, Slack, Microsoft Teams.
* **Security Scanning:** Tích hợp các công cụ quét lỗ hổng bảo mật.
* **Rollback Strategies:** Xây dựng cơ chế rollback.
* **Dynamic Tagging:** Sử dụng Git commit hash hoặc timestamp cho `IMAGE_TAG`.
    ```groovy
    // Ví dụ tag bằng Git commit hash ngắn
    // IMAGE_TAG = sh(script: 'git rev-parse --short HEAD', returnStdout: true).trim()
    ```
* **Sử dụng Docker Hub Access Token:** Thay vì password, tạo Access Token trên Docker Hub và dùng nó làm password trong Jenkins credential.

---

## Phần 8: Gỡ Lỗi (Troubleshooting) Phổ Biến

* **Permission Denied (SSH):**
    * Đảm bảo public key từ Jenkins credential (`prod-ssh-key`) đã được thêm **chính xác** vào file `~/.ssh/authorized_keys` trên server đích cho đúng user.
    * Kiểm tra quyền của thư mục `~/.ssh` (`700`) và file `authorized_keys` (`600`) trên server đích.
    * Kiểm tra log `sshd` trên server đích (`/var/log/auth.log`, `/var/log/secure`, hoặc `journalctl -u sshd -f`).
    * Đảm bảo `Username` trong SSH credential của Jenkins khớp với user trên server đích.

* **Docker Login Failed:**
    * Kiểm tra lại Docker Hub username và password/access token trong Jenkins credential.
    * Đảm bảo ID credential trong Jenkinsfile (`env.DOCKERHUB_CREDENTIALS_ID`) khớp với ID bạn đã tạo.

* **Docker Build Failed:**
    * Kiểm tra lỗi trong `Dockerfile`.
    * Kiểm tra log build chi tiết trong Console Output của Jenkins.

* **Plugin `sshCommand` Not Found (`NoSuchMethodError`):**
    * Đảm bảo plugin "SSH Pipeline Steps" đã được cài đặt và kích hoạt.

* **`allowAnyHosts = true` không hoạt động:**
    * Thử thay thế bằng `knownHosts: 'NONE'` trong map `remote` của `sshCommand`.

---

## Kết luận
Việc thiết lập CI/CD là một quá trình đầu tư ban đầu nhưng mang lại lợi ích to lớn về lâu dài. Hướng dẫn này cung cấp một nền tảng cơ bản. Bạn có thể và nên tùy chỉnh nó để phù hợp với nhu cầu của dự án.

Chúc bạn thành công!
