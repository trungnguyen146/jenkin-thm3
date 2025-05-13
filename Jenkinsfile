pipeline {
    agent {
        docker {
            image 'docker:dind'
            args '-v /var/run/docker.sock:/var/run/docker.sock --privileged'
        }
    }

    environment {
        DOCKERHUB_USERNAME = 'trungnguyen146'
        IMAGE_NAME = 'trungnguyen146/php-website'
        IMAGE_TAG = 'ver1'
        FULL_IMAGE = "${IMAGE_NAME}:${IMAGE_TAG}"
        DOCKERHUB_CREDENTIALS = credentials('github-pat') // ID Docker Hub credential

        // Staging (Local - Same as Jenkins)
        CONTAINER_NAME_STAGING_LOCAL = 'php-container-staging'
        HOST_PORT_STAGING_LOCAL = 8800
        APPLICATION_PORT = 80

        // Production VPS
        VPS_PRODUCTION_CREDENTIALS_ID = 'Prod_CredID'
        VPS_PRODUCTION_HOST = '14.225.219.14'
        CONTAINER_NAME_PRODUCTION = 'php-container-prod'
        HOST_PORT_PRODUCTION = 80
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
                script {
                    echo "🔐 Logging in to Docker Hub..."
                    sh """
                        echo "\${DOCKERHUB_CREDENTIALS_PSW}" | docker login -u "\${DOCKERHUB_CREDENTIALS_USR}" --password-stdin
                    """
                }
            }
        }

        stage('Build and Push Image') {
            steps {
                script {
                    echo "🚧 Building and pushing image: ${FULL_IMAGE}"
                    sh """
                        docker buildx build -t \${FULL_IMAGE} -f Dockerfile . --push || {
                            echo "⚠️ buildx failed, falling back to classic build"
                            docker build -t \${FULL_IMAGE} -f Dockerfile .
                            docker push \${FULL_IMAGE}
                        }
                    """
                }
            }
        }

        stage('Deploy to Staging (Local)') {
            steps {
                script {
                    echo "🚀 Deploying container to Staging (Local)..."
                    sh "docker pull ${FULL_IMAGE}"
                    sh """
                        docker stop \${CONTAINER_NAME_STAGING_LOCAL} || true
                        docker rm \${CONTAINER_NAME_STAGING_LOCAL} || true
                    """
                    sh "docker run -d --name \${CONTAINER_NAME_STAGING_LOCAL} -p \${HOST_PORT_STAGING_LOCAL}:\${APPLICATION_PORT} ${FULL_IMAGE}"
                    echo "✅ Deployed to Staging (Local) on port ${HOST_PORT_STAGING_LOCAL}"
                }
            }
        }

        // stage('Approve Production Deployment') {
        //     steps {
        //         input message: 'Approve deployment to Production?'
        //     }
        // }

          stage('Test Production SSH Connection') {
            when {
                expression { return currentBuild.result == null || currentBuild.result == 'SUCCESS' }
            }
            steps {
                withCredentials([sshUserPrivateKey(credentialsId: "${VPS_PRODUCTION_CREDENTIALS_ID}", keyFileVariable: 'SSH_PRIVATE_KEY_FILE', usernameVariable: 'SSH_USER')]) {
                    script {
                        def SSH_HOST = "${VPS_PRODUCTION_HOST}"

                        echo "🩺 Testing SSH connection to Production (${SSH_HOST})..."
                        echo "SSH_USER: ${SSH_USER}"

                        sh """
                            ssh -o StrictHostKeyChecking=no -i "\$SSH_PRIVATE_KEY_FILE" -T "\$SSH_USER@${SSH_HOST}" -p 22 -o ConnectTimeout=10 'echo Connected successfully'
                        """
                    }
                }
            }
        }
        

        // Test
        // stage('Test Production SSH Connection') {
        //     when {
        //         expression { return currentBuild.result == null || currentBuild.result == 'SUCCESS' }
        //     }
        //     steps {
        //         withCredentials([(credentialsId: "${VPS_PRODUCTION_CREDENTIALS_ID}", variable: 'SSH_PRIVATE_KEY')]) {
        //             script {
        //                 def SSH_USER = 'root'
        //                 def SSH_HOST = "${VPS_PRODUCTION_HOST}"

        //                 echo "🩺 Testing SSH connection to Production (${SSH_HOST})..."
        //                 echo "SSH_USER: ${SSH_USER}"
        //                 echo "SSH_HOST: ${SSH_HOST}"

        //                 sh """
        //                     echo "$SSH_PRIVATE_KEY" > id_rsa
        //                     chmod 400 id_rsa
        //                     ssh -o StrictHostKeyChecking=no -T ${SSH_USER}@${SSH_HOST} -p 22 -o ConnectTimeout=10 'echo Connected successfully'
        //                     rm -f id_rsa
        //                 """
        //             }
        //         }
        //     }
        // }


    //     stage('Deploy to Production') {
    //         when {
    //             expression { return currentBuild.result == null || currentBuild.result == 'SUCCESS' }
    //         }
    //         steps {
    //             input message: 'Proceed with deployment to Production?'
    //             withCredentials([sshUserPrivateKey(credentialsId: "${VPS_PRODUCTION_CREDENTIALS_ID}")]) {
    //                 script {
    //                     def SSH_USER = sshUser
    //                     def SSH_HOST = "${VPS_PRODUCTION_HOST}"
    //                     echo "🚀 Deploying to Production (${SSH_HOST}:${HOST_PORT_PRODUCTION})..."
    //                     sh """
    //                         ssh -o StrictHostKeyChecking=no \${SSH_USER}@\${SSH_HOST} '
    //                             docker pull ${FULL_IMAGE}
    //                             docker stop \${CONTAINER_NAME_PRODUCTION} || true
    //                             docker rm \${CONTAINER_NAME_PRODUCTION} || true
    //                             docker run -d --name \${CONTAINER_NAME_PRODUCTION} -p \${HOST_PORT_PRODUCTION}:\${APPLICATION_PORT} ${FULL_IMAGE}
    //                             echo "✅ Deployed to Production"
    //                         '
    //                     """
    //                 }
    //             }
    //         }
    //     }

    }

    post {
        always {
            echo '🧹 Cleaning up...'
            sh 'docker system prune -f'
        }
        success {
            echo "🎉 Pipeline finished successfully!"
        }
        failure {
            echo "💔 Pipeline failed. Check logs."
        }
    }
}
