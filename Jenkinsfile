pipeline {
    agent any

    environment {
        VPS_PRODUCTION_HOST = '14.225.219.14'      // Thay bằng IP hoặc hostname VPS của bạn
        SSH_CREDENTIALS_ID = 'Prod_CredID'         // Thay bằng ID credential SSH key của bạn
        SSH_USERNAME = 'root'                     // Thay bằng username SSH của bạn (nếu cần)
    }

stages {
    stage('Test SSH Connection with Key (Manual)') {
        steps {
            script {
                def SSH_HOST = "${VPS_PRODUCTION_HOST}"
                def SSH_USER = "${env.SSH_USERNAME}" // Sử dụng biến môi trường

                withCredentials([sshUserPrivateKey(credentialsId: "${SSH_CREDENTIALS_ID}", keyFileVariable: 'TEMP_SSH_KEY')]) {
                    sh """
                        echo "🩺 Testing SSH connection to ${SSH_USER}@${SSH_HOST} using SSH key (manual)..."
                        chmod 400 "\$TEMP_SSH_KEY" // TEMP_SSH_KEY là biến môi trường do withCredentials cung cấp, nên \$TEMP_SSH_KEY là đúng
                        ssh -o StrictHostKeyChecking=no -i "\$TEMP_SSH_KEY" "${SSH_USER}@${SSH_HOST}" -p 22 -o ConnectTimeout=10 'echo Connected successfully'
                    """
                }
            }
        }
    }








// pipeline {
//     agent {
//         docker {
//             image 'docker:dind'
//             args '-v /var/run/docker.sock:/var/run/docker.sock --privileged'
//         }
//     }

//     environment {
//         DOCKERHUB_USERNAME = 'trungnguyen146'
//         IMAGE_NAME = 'trungnguyen146/php-website'
//         IMAGE_TAG = 'ver1'
//         FULL_IMAGE = "${IMAGE_NAME}:${IMAGE_TAG}"
//         DOCKERHUB_CREDENTIALS = credentials('github-pat') // ID Docker Hub credential

//         // Staging (Local - Same as Jenkins)
//         CONTAINER_NAME_STAGING_LOCAL = 'php-container-staging'
//         HOST_PORT_STAGING_LOCAL = 8800
//         APPLICATION_PORT = 80

//         // Production VPS
//         // VPS_PRODUCTION_CREDENTIALS_ID = credentials('Prod_CredID')
//         VPS_PRODUCTION_HOST = '14.225.219.14'
//         CONTAINER_NAME_PRODUCTION = 'php-container-prod'
//         HOST_PORT_PRODUCTION = 80
//         SSH_CREDENTIALS_ID = 'Prod_CredID'
//     }

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
//                 script {
//                     echo "🔐 Logging in to Docker Hub..."
//                     sh """
//                         echo "\${DOCKERHUB_CREDENTIALS_PSW}" | docker login -u "\${DOCKERHUB_CREDENTIALS_USR}" --password-stdin
//                     """
//                 }
//             }
//         }

//         stage('Build and Push Image') {
//             steps {
//                 script {
//                     echo "🚧 Building and pushing image: ${FULL_IMAGE}"
//                     sh """
//                         docker buildx build -t \${FULL_IMAGE} -f Dockerfile . --push || {
//                             echo "⚠️ buildx failed, falling back to classic build"
//                             docker build -t \${FULL_IMAGE} -f Dockerfile .
//                             docker push \${FULL_IMAGE}
//                         }
//                     """
//                 }
//             }
//         }


//          stage('Test SSH Connection with Key') {
//             steps {
//                 withCredentials([sshUserPrivateKey(credentialsId: 'Prod_CredID', usernameVariable: 'SSH_USER')]) {
//                     script {
//                         def SSH_HOST = "${VPS_PRODUCTION_HOST}"
//                         def SSH_USER = "${SSH_USER}"

//                         echo "🩺 Testing SSH connection to ${SSH_USER}@${SSH_HOST} using SSH key..."

//                         sh """
//                             ssh -o StrictHostKeyChecking=no -T "\$SSH_USER@${SSH_HOST}" -p 22 -o ConnectTimeout=10 'echo Connected successfully'
//                         """
//                     }
//                 }
//             }
//         }



//         stage('Deploy to Production') {
//             when {
//                 expression { return currentBuild.result == null || currentBuild.result == 'SUCCESS' }
//             }
//             steps {
//                 input message: 'Proceed with deployment to Production?'
//                 withCredentials([sshUserPrivateKey(credentialsId: 'Prod_CredID', usernameVariable: 'SSH_USER')]) {
//                     script {
//                         def SSH_USER = "${SSH_USER}"
//                         def SSH_HOST = "${VPS_PRODUCTION_HOST}"
//                         echo "🚀 Deploying to Production (${SSH_HOST}:${HOST_PORT_PRODUCTION})..."
//                         sh """
//                             ssh -o StrictHostKeyChecking=no \${SSH_USER}@\${SSH_HOST} '
//                                 docker pull ${FULL_IMAGE}
//                                 docker stop \${CONTAINER_NAME_PRODUCTION} || true
//                                 docker rm \${CONTAINER_NAME_PRODUCTION} || true
//                                 docker run -d --name \${CONTAINER_NAME_PRODUCTION} -p \${HOST_PORT_PRODUCTION}:\${APPLICATION_PORT} ${FULL_IMAGE}
//                                 echo "✅ Deployed to Production"
//                             '
//                         """
//                     }
//                 }
//             }
//         }

//     }

//     post {
//         always {
//             echo '🧹 Cleaning up...'
//             sh 'docker system prune -f'
//         }
//         success {
//             echo "🎉 Pipeline finished successfully!"
//         }
//         failure {
//             echo "💔 Pipeline failed. Check logs."
//         }
//     }
// }
