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
        IMAGE_TAG = 'ver1' // Cân nhắc dùng tag động: "ver${env.BUILD_NUMBER}"
        FULL_IMAGE = "${IMAGE_NAME}:${IMAGE_TAG}"
        DOCKERHUB_CREDENTIALS_ID = 'github-pat'

        APPLICATION_PORT = 80

        VPS_PRODUCTION_HOST = '14.225.219.14'
        CONTAINER_NAME_PRODUCTION = 'php-container-prod'
        HOST_PORT_PRODUCTION = 80
        SSH_CREDENTIALS_ID = 'Prod_CredID' // ID của Jenkins Credential loại "SSH Username with private key"
    }


stage('Test SSH Simple') {
    steps {
        sshagent(['Prod_CredID']) {
            sh "ssh -o StrictHostKeyChecking=no root@${env.VPS_PRODUCTION_HOST} 'echo \"SSH connection successful\"'"
        }
    }
}

//     triggers {
//         pollSCM('H/2 * * * *')
//     }

//     stages {
//         stage('Checkout') {
//             steps {
//                 checkout scm
//             }
//         }

//         stage('Login to Docker Hub') {
//             steps {
//                 withCredentials([usernamePassword(credentialsId: "${env.DOCKERHUB_CREDENTIALS_ID}", usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
//                     script {
//                         echo "🔐 Logging in to Docker Hub as ${DOCKER_USER}..."
//                         sh """
//                             echo "\${DOCKER_PASS}" | docker login -u "\${DOCKER_USER}" --password-stdin
//                         """
//                     }
//                 }
//             }
//         }

//         stage('Build and Push Image') {
//             steps {
//                 script {
//                     echo "🚧 Building and pushing image: ${env.FULL_IMAGE}"
//                     sh """
//                         docker buildx build -t "${env.FULL_IMAGE}" -f Dockerfile . --push || {
//                             echo "⚠️ buildx failed, falling back to classic build"
//                             docker build -t "${env.FULL_IMAGE}" -f Dockerfile .
//                             docker push "${env.FULL_IMAGE}"
//                         }
//                     """
//                 }
//             }
//         }




//     stage('Test SSH Connection with Key (using SSH Steps)') {
//     steps {
//         script {
//             withCredentials([sshUserPrivateKey(
//                 credentialsId: "${env.SSH_CREDENTIALS_ID}",
//                 keyFileVariable: 'sshKeyFile',
//                 usernameVariable: 'sshUser'
//             )]) {
//                 def remote = [
//                     name: "production-vps-test",
//                     host: env.VPS_PRODUCTION_HOST,
//                     user: sshUser,
//                     identityFile: sshKeyFile,
//                     allowAnyHosts: true
//                 ]
//                 echo "🩺 Đang kiểm tra kết nối SSH tới ${sshUser}@${env.VPS_PRODUCTION_HOST} ..."
//                 sshCommand remote: remote, command: 'echo "✅ SSH kết nối thành công đến $(hostname)"'
//             }
//         }
//     }
// }

        

//         // stage('Test SSH Connection with Key (using SSH Steps)') { // Đã cập nhật tên stage cho rõ ràng
//         //     steps {
//         //         script {
//         //             // 1. Khởi tạo map 'remoteTestConfig'
//         //             def remoteTestConfig = [:]
                    
//         //             // 2. Điền thông tin cơ bản
//         //             remoteTestConfig.name = "production-vps-test"      // Tên mô tả
//         //             remoteTestConfig.host = env.VPS_PRODUCTION_HOST     // IP host từ biến môi trường
//         //             remoteTestConfig.allowAnyHosts = true               // Giống ví dụ của bạn. Nếu lỗi, thử: knownHosts: 'NONE'
//         //             // remoteTestConfig.port = 22                       // Port mặc định là 22, có thể bỏ qua
//         //             // remoteTestConfig.options = [ConnectTimeout: '10'] // Tùy chọn timeout, nếu plugin hỗ trợ dạng này

//         //             // 3. Lấy credential và hoàn thiện map
//         //             withCredentials([sshUserPrivateKey(
//         //                 credentialsId: "${env.SSH_CREDENTIALS_ID}",
//         //                 keyFileVariable: 'testKeyFile',          // Tên biến cho file key
//         //                 passphraseVariable: '',                  // Để trống nếu key không có passphrase
//         //                 usernameVariable: 'testSshUsername'      // Tên biến cho username
//         //             )]) {
//         //                 remoteTestConfig.user = testSshUsername
//         //                 remoteTestConfig.identityFile = testKeyFile

//         //                 echo "🩺 Đang kiểm tra kết nối SSH tới ${remoteTestConfig.user}@${remoteTestConfig.host} bằng SSH Steps plugin..."
                        
//         //                 // 4. Thực thi lệnh test
//         //                 def testConnectionCommand = 'echo "✅ Kết nối SSH Steps thành công tới $(hostname) với tư cách $(whoami)! Ngày giờ server: $(date)"'
//         //                 sshCommand remote: remoteTestConfig, command: testConnectionCommand
                        
//         //                 echo "✅ Kiểm tra kết nối SSH bằng SSH Steps thành công."
//         //             }
//         //         }
//         //     }
//         // }

//         // stage('Deploy to Production') {
//         //     when {
//         //         expression { return currentBuild.result == null || currentBuild.result == 'SUCCESS' }
//         //     }
//         //     steps {
//         //         input message: "Proceed with deployment of ${env.FULL_IMAGE} to Production?"
                
//         //         // "Cách 1" được cập nhật để sử dụng SSH Steps plugin
//         //         script {
//         //             // 1. Khởi tạo map 'remoteDeployConfig'
//         //             def remoteDeployConfig = [:]

//         //             // 2. Điền thông tin cơ bản
//         //             remoteDeployConfig.name = "production-vps-deploy"
//         //             remoteDeployConfig.host = env.VPS_PRODUCTION_HOST
//         //             remoteDeployConfig.allowAnyHosts = true // Nếu lỗi, thử: knownHosts: 'NONE'
//         //             // remoteDeployConfig.port = 22

//         //             // 3. Lấy credential và hoàn thiện map
//         //             withCredentials([sshUserPrivateKey(
//         //                 credentialsId: "${env.SSH_CREDENTIALS_ID}",
//         //                 keyFileVariable: 'deployKeyFile',
//         //                 passphraseVariable: '',
//         //                 usernameVariable: 'deploySshUsername'
//         //             )]) {
//         //                 remoteDeployConfig.user = deploySshUsername
//         //                 remoteDeployConfig.identityFile = deployKeyFile

//         //                 echo "🚀 Đang triển khai tới Production (${remoteDeployConfig.user}@${remoteDeployConfig.host}:${env.HOST_PORT_PRODUCTION}) bằng SSH Steps plugin..."
                        
//         //                 // 4. Chuẩn bị chuỗi lệnh deploy (sử dụng GString để ${env.VAR} được nội suy bởi Groovy)
//         //                 def deployCommands = """
//         //                     echo 'Pulling image ${env.FULL_IMAGE}...' && \\
//         //                     docker pull '${env.FULL_IMAGE}' && \\
//         //                     echo 'Stopping container ${env.CONTAINER_NAME_PRODUCTION}...' && \\
//         //                     docker stop '${env.CONTAINER_NAME_PRODUCTION}' || true && \\
//         //                     echo 'Removing container ${env.CONTAINER_NAME_PRODUCTION}...' && \\
//         //                     docker rm '${env.CONTAINER_NAME_PRODUCTION}' || true && \\
//         //                     echo 'Running new container ${env.CONTAINER_NAME_PRODUCTION}...' && \\
//         //                     docker run -d --name '${env.CONTAINER_NAME_PRODUCTION}' -p '${env.HOST_PORT_PRODUCTION}:${env.APPLICATION_PORT}' '${env.FULL_IMAGE}' && \\
//         //                     echo '✅ Đã triển khai lên Production'
//         //                 """
                        
//         //                 // 5. Thực thi lệnh deploy
//         //                 sshCommand remote: remoteDeployConfig, command: deployCommands
                        
//         //                 echo "✅ Các lệnh triển khai đã được gửi qua SSH Steps plugin."
//         //             }
//         //         }

//         //         // "Cách 2": Sử dụng SSH Agent Plugin (vẫn giữ nguyên, đang được comment out)
//         //         /*
//         //         sshagent(credentials: ["${env.SSH_CREDENTIALS_ID}"]) {
//         //             // ... (code cho SSH Agent như trước) ...
//         //         }
//         //         */
//         //     }
//         // }
//     } // Kết thúc khối stages

//     post {
//         always {
//             echo '🧹 Cleaning up Docker system on agent...'
//             sh 'docker system prune -af'
//         }
//         success {
//             echo "🎉 Pipeline finished successfully!"
//         }
//         failure {
//             echo "💔 Pipeline failed. Check logs."
//         }
//     }
// } // Kết thúc khối pipeline

