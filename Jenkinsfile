// pipeline {
//     agent any

//     environment {
//         VPS_PRODUCTION_HOST = '14.225.219.14'      // Thay bằng IP hoặc hostname VPS của bạn
//         SSH_CREDENTIALS_ID = 'Prod_CredID'         // Thay bằng ID credential SSH key của bạn
//         SSH_USERNAME = 'root'                     // Thay bằng username SSH của bạn (nếu cần)
//     }

//  stages {
//     stage('Test SSH Connection with Key (Manual)') {
//         steps {
//             script {
//                 def SSH_HOST = "${VPS_PRODUCTION_HOST}"
//                 def SSH_USER = "${env.SSH_USERNAME}" // Sử dụng biến môi trường

//                 withCredentials([sshUserPrivateKey(credentialsId: "${SSH_CREDENTIALS_ID}", keyFileVariable: 'TEMP_SSH_KEY')]) {
//                     sh """
//                         echo "🩺 Testing SSH connection to ${SSH_USER}@${SSH_HOST} using SSH key (manual)..."
//                         chmod 400 "\$TEMP_SSH_KEY" // TEMP_SSH_KEY là biến môi trường do withCredentials cung cấp, nên \$TEMP_SSH_KEY là đúng
//                         ssh -o StrictHostKeyChecking=no -i "\$TEMP_SSH_KEY" "${SSH_USER}@${SSH_HOST}" -p 22 -o ConnectTimeout=10 'echo Connected successfully'
//                     """
//                 }
//             }
//         }
//     }
// }



pipeline {
    agent {
        docker {
            image 'docker:dind' // Sử dụng Docker-in-Docker
            args '--privileged'  // dind thường cần --privileged để chạy daemon Docker riêng bên trong container.
                                 // Việc mount /var/run/docker.sock không cần thiết khi dùng dind.
        }
    }

    environment {
        DOCKERHUB_USERNAME = 'trungnguyen146' // Có thể không cần nếu credential đã chứa username
        IMAGE_NAME = 'trungnguyen146/php-website'
        // Nên sử dụng tag động để dễ quản lý phiên bản, ví dụ:
        // IMAGE_TAG = "ver${env.BUILD_NUMBER}" 
        // hoặc IMAGE_TAG = sh(script: 'git rev-parse --short HEAD', returnStdout: true).trim()
        IMAGE_TAG = 'ver1' // Giữ lại tag tĩnh của bạn, nhưng cân nhắc thay đổi
        FULL_IMAGE = "${IMAGE_NAME}:${IMAGE_TAG}"
        // Đảm bảo 'github-pat' là ID của Jenkins Credential loại "Username with password" cho Docker Hub
        DOCKERHUB_CREDENTIALS_ID = 'github-pat' 

        // Staging (Local - Same as Jenkins) - Hiện tại không được sử dụng trong các stages bên dưới
        // CONTAINER_NAME_STAGING_LOCAL = 'php-container-staging'
        // HOST_PORT_STAGING_LOCAL = 8800
        APPLICATION_PORT = 80 // Port của ứng dụng bên trong container

        // Production VPS
        VPS_PRODUCTION_HOST = '14.225.219.14'
        CONTAINER_NAME_PRODUCTION = 'php-container-prod'
        HOST_PORT_PRODUCTION = 80 // Port trên VPS để map với APPLICATION_PORT
        SSH_CREDENTIALS_ID = 'Prod_CredID' // ID của Jenkins Credential loại "SSH Username with private key"
    }

    triggers {
        pollSCM('H/2 * * * *') // Kiểm tra SCM mỗi 2 phút
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Login to Docker Hub') {
            steps {
                // Sử dụng withCredentials để truy cập username và password một cách an toàn
                withCredentials([usernamePassword(credentialsId: "${env.DOCKERHUB_CREDENTIALS_ID}", usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                    script {
                        echo "🔐 Logging in to Docker Hub as ${DOCKER_USER}..."
                        sh """
                            echo "\${DOCKER_PASS}" | docker login -u "\${DOCKER_USER}" --password-stdin
                        """
                    }
                }
            }
        }

        stage('Build and Push Image') {
            steps {
                script {
                    echo "🚧 Building and pushing image: ${env.FULL_IMAGE}"
                    sh """
                        docker buildx build -t "${env.FULL_IMAGE}" -f Dockerfile . --push || {
                            echo "⚠️ buildx failed, falling back to classic build"
                            docker build -t "${env.FULL_IMAGE}" -f Dockerfile .
                            docker push "${env.FULL_IMAGE}"
                        }
                    """
                    // Sử dụng "${env.FULL_IMAGE}" để Groovy nội suy biến trước khi truyền cho shell
                }
            }
        }


        /*
        stage('Test SSH Connection with Key') {
            steps {
                script {
                    withCredentials([sshUserPrivateKey(credentialsId: "${env.SSH_CREDENTIALS_ID}", keyFileVariable: 'TEMP_SSH_KEY', usernameVariable: 'SSH_USER')]) {
                        echo "🩺 Testing SSH connection to ${SSH_USER}@${env.VPS_PRODUCTION_HOST} using SSH key..."
                        sh """
                            chmod 400 "\$TEMP_SSH_KEY"
                            ssh -o StrictHostKeyChecking=no -i "\$TEMP_SSH_KEY" "${SSH_USER}@${env.VPS_PRODUCTION_HOST}" -p 22 -o ConnectTimeout=10 'echo Connected successfully'
                        """
                    }
                }
            }
        }

        stage('Deploy to Production') {
            when {
                // Chỉ chạy khi các stage trước thành công (result là null khi đang chạy, SUCCESS khi hoàn thành tốt)
                expression { return currentBuild.result == null || currentBuild.result == 'SUCCESS' }
            }
            steps {
                input message: "Proceed with deployment of ${env.FULL_IMAGE} to Production?"
                
                // Cách 1: Sử dụng sshUserPrivateKey (như bạn đang làm)
                withCredentials([sshUserPrivateKey(credentialsId: "${env.SSH_CREDENTIALS_ID}", keyFileVariable: 'PROD_SSH_KEY', usernameVariable: 'PROD_SSH_USER')]) {
                    script {
                        echo "🚀 Deploying to Production (${env.VPS_PRODUCTION_HOST}:${env.HOST_PORT_PRODUCTION})..."
                        // Các biến Jenkins (env.FULL_IMAGE, env.CONTAINER_NAME_PRODUCTION, ...) cần được Groovy nội suy
                        // vào chuỗi lệnh sẽ được thực thi trên server từ xa.
                        sh """
                            ssh -o StrictHostKeyChecking=no -i "\$PROD_SSH_KEY" "${PROD_SSH_USER}@${env.VPS_PRODUCTION_HOST}" " \\
                                echo 'Pulling image ${env.FULL_IMAGE}...' && \\
                                docker pull '${env.FULL_IMAGE}' && \\
                                echo 'Stopping container ${env.CONTAINER_NAME_PRODUCTION}...' && \\
                                docker stop '${env.CONTAINER_NAME_PRODUCTION}' || true && \\
                                echo 'Removing container ${env.CONTAINER_NAME_PRODUCTION}...' && \\
                                docker rm '${env.CONTAINER_NAME_PRODUCTION}' || true && \\
                                echo 'Running new container ${env.CONTAINER_NAME_PRODUCTION}...' && \\
                                docker run -d --name '${env.CONTAINER_NAME_PRODUCTION}' -p '${env.HOST_PORT_PRODUCTION}:${env.APPLICATION_PORT}' '${env.FULL_IMAGE}' && \\
                                echo '✅ Deployed to Production' \\
                            "
                        """
                        // Lưu ý: Các lệnh docker trên được nối với nhau bằng && để đảm bảo dừng lại nếu có lỗi (trừ stop/rm dùng || true).
                        // Dấu \ ở cuối dòng để nối chuỗi lệnh dài cho dễ đọc trong Groovy.
                    }
                }
             */ // Kết thúc khối SSH key 

                // Cách 2: Sử dụng SSH Agent Plugin (cách kết nối "mới" và thường được khuyến nghị hơn cho nhiều lệnh SSH)
                // Bạn cần cài đặt plugin "SSH Agent" trong Jenkins.

                   // bắt đầu khối SSH Agent Plugin
                sshagent(credentials: ["${env.SSH_CREDENTIALS_ID}"]) { // Truyền ID của SSH credential
                    script {
                        def sshUser = '' // Lấy username từ credential nếu có, hoặc định nghĩa ở đây/env
                        // Nếu credential 'Prod_CredID' của bạn là "SSH Username with private key" và đã có username (vd: 'root')
                        // thì sshagent sẽ tự động sử dụng username đó.
                        // Nếu không, bạn cần lấy username:
                        // withCredentials([sshUserPrivateKey(credentialsId: "${env.SSH_CREDENTIALS_ID}", usernameVariable: 'PROD_SSH_USER_AGENT')]) {
                        //    sshUser = PROD_SSH_USER_AGENT
                        // }
                        // Giả sử username là 'root' hoặc đã có trong credential
                        // def sshTarget = "root@${env.VPS_PRODUCTION_HOST}" // Thay 'root' nếu cần

                        // Lấy username từ credential một cách an toàn
                        def sshTarget = ''
                        withCredentials([sshUserPrivateKey(credentialsId: "${env.SSH_CREDENTIALS_ID}", usernameVariable: 'PROD_SSH_USER_AGENT')]) {
                           sshTarget = "${PROD_SSH_USER_AGENT}@${env.VPS_PRODUCTION_HOST}"
                        }

                        echo "🚀 Deploying to Production using SSH Agent (${sshTarget}:${env.HOST_PORT_PRODUCTION})..."
                        sh """
                            ssh -o StrictHostKeyChecking=no "${sshTarget}" " \\
                                echo 'Pulling image ${env.FULL_IMAGE}...' && \\
                                docker pull '${env.FULL_IMAGE}' && \\
                                echo 'Stopping container ${env.CONTAINER_NAME_PRODUCTION}...' && \\
                                docker stop '${env.CONTAINER_NAME_PRODUCTION}' || true && \\
                                echo 'Removing container ${env.CONTAINER_NAME_PRODUCTION}...' && \\
                                docker rm '${env.CONTAINER_NAME_PRODUCTION}' || true && \\
                                echo 'Running new container ${env.CONTAINER_NAME_PRODUCTION}...' && \\
                                docker run -d --name '${env.CONTAINER_NAME_PRODUCTION}' -p '${env.HOST_PORT_PRODUCTION}:${env.APPLICATION_PORT}' '${env.FULL_IMAGE}' && \\
                                echo '✅ Deployed to Production' \\
                            "
                        """
                    }
                }
                 // Kết thúc khối SSH Agent 
            }
        }
    } // Kết thúc khối stages chính

    post {
        always {
            echo '🧹 Cleaning up Docker system on agent...'
            sh 'docker system prune -af' // -a để xóa cả images không dùng, -f để không hỏi confirm
        }
        success {
            echo "🎉 Pipeline finished successfully!"
        }
        failure {
            echo "💔 Pipeline failed. Check logs."
        }
    }
} // Kết thúc khối pipeline




