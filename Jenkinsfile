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
            image 'docker:dind'
            args '--privileged'
        }
    }

    environment {
        DOCKERHUB_USERNAME = 'trungnguyen146'
        IMAGE_NAME = 'trungnguyen146/php-website'
        IMAGE_TAG = 'ver1'
        FULL_IMAGE = "${IMAGE_NAME}:${IMAGE_TAG}"
        DOCKERHUB_CREDENTIALS_ID = 'github-pat'

        APPLICATION_PORT = 80

        VPS_PRODUCTION_HOST = '14.225.219.14'
        CONTAINER_NAME_PRODUCTION = 'php-container-prod'
        HOST_PORT_PRODUCTION = 80
        SSH_CREDENTIALS_ID = 'Prod_CredID' // ID của Jenkins Credential loại "SSH Username with private key"
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

        stage('Login to Docker Hub') {
            steps {
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
                }
            }
        }

        /*
        stage('Test SSH Connection with Key') {
            // ... (đã được comment out) ...
        }
        */

        stage('Deploy to Production') {
            when {
                expression { return currentBuild.result == null || currentBuild.result == 'SUCCESS' }
            }
            steps {
                input message: "Proceed with deployment of ${env.FULL_IMAGE} to Production?"
                
                /* // Cách 1: Sử dụng sshUserPrivateKey (đã được comment out)
                withCredentials([sshUserPrivateKey(credentialsId: "${env.SSH_CREDENTIALS_ID}", keyFileVariable: 'PROD_SSH_KEY', usernameVariable: 'PROD_SSH_USER')]) {
                    // ...
                }
                */

                // Cách 2: Sử dụng SSH Agent Plugin
                // Đảm bảo plugin "SSH Agent" đã được cài đặt trong Jenkins.
                // Đảm bảo credential "${env.SSH_CREDENTIALS_ID}" (Prod_CredID) là loại "SSH Username with private key"
                // và có username (ví dụ: 'root') được điền trong cấu hình credential đó trên Jenkins.
                sshagent(credentials: ["${env.SSH_CREDENTIALS_ID}"]) {
                    script {
                        // Biến usernameVariable 'PROD_SSH_USER_FROM_AGENT' sẽ chứa username từ credential.
                        // Điều này hữu ích để đảm bảo bạn đang sử dụng đúng username.
                        def sshLoginUser = ''
                        withCredentials([sshUserPrivateKey(credentialsId: "${env.SSH_CREDENTIALS_ID}", usernameVariable: 'PROD_SSH_USER_FROM_AGENT')]) {
                            if (PROD_SSH_USER_FROM_AGENT == null || PROD_SSH_USER_FROM_AGENT.trim().isEmpty()) {
                                echo "Warning: SSH Username is empty in credential '${env.SSH_CREDENTIALS_ID}'. You may need to explicitly set the user in the ssh command (e.g., 'root@host')."
                                // Nếu username trống, lệnh ssh có thể thất bại hoặc dùng user mặc định của agent.
                                // Trong trường hợp này, bạn nên cấu hình username trong credential trên Jenkins
                                // hoặc sửa thành: sshLoginUser = 'root' (nếu 'root' là user bạn muốn)
                                error("SSH Username is missing in credential '${env.SSH_CREDENTIALS_ID}'. Please configure it in Jenkins.")
                            }
                            sshLoginUser = PROD_SSH_USER_FROM_AGENT
                        }
                        
                        def sshTarget = "${sshLoginUser}@${env.VPS_PRODUCTION_HOST}"

                        echo "🚀 Deploying to Production using SSH Agent (${sshTarget}:${env.HOST_PORT_PRODUCTION})..."
                        sh """
                            # Lệnh ssh bây giờ không cần -i keyfile vì SSH agent sẽ cung cấp key.
                            # Username được lấy từ credential và truyền vào biến sshTarget.
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
    } // Kết thúc stages

    post {
        always {
            echo '🧹 Cleaning up Docker system on agent...'
            sh 'docker system prune -af'
        }
        success {
            echo "🎉 Pipeline finished successfully!"
        }
        failure {
            echo "💔 Pipeline failed. Check logs."
        }
    }
} // Kết thúc khối pipeline



