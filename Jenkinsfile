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

        // Staging VPS
        VPS_STAGING_CREDENTIALS_ID = 'Stag_CredID'
        VPS_STAGING_HOST = '14.225.219.164'
        CONTAINER_NAME_STAGING = 'php-container-staging' // Tên container staging khác

        // Production VPS
        VPS_PRODUCTION_CREDENTIALS_ID = 'Prod_CredID'
        VPS_PRODUCTION_HOST = '14.225.219.14'
        CONTAINER_NAME_PRODUCTION = 'php-container-prod' // Tên container production

        APPLICATION_PORT = 80
        HOST_PORT_STAGING = 8888
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
                    sh "echo \"${DOCKERHUB_CREDENTIALS_PSW}\" | docker login -u \"${DOCKERHUB_CREDENTIALS_USR}\" --password-stdin"
                }
            }
        }

        stage('Build and Push Image') {
            steps {
                script {
                    echo "🚧 Building and pushing image: ${FULL_IMAGE}"
                    sh """
                        docker buildx build -t ${FULL_IMAGE} -f Dockerfile . --push || {
                            echo "⚠️ buildx failed, falling back to classic build"
                            docker build -t ${FULL_IMAGE} -f Dockerfile .
                            docker push ${FULL_IMAGE}
                        }
                    """
                }
            }
        }

        stage('Deploy to Staging') {
            steps {
                withCredentials([sshUserPrivateKey(credentialsId: "${VPS_STAGING_CREDENTIALS_ID}")]) {
                    script {
                        def SSH_USER = "${sshUser}"
                        def SSH_HOST = "${VPS_STAGING_HOST}"
                        echo "🚀 Deploying to Staging (${SSH_HOST}:${env.HOST_PORT_STAGING})..."
                        sh """
                            ssh -o StrictHostKeyChecking=no ${SSH_USER}@${SSH_HOST} '
                                docker pull ${FULL_IMAGE}
                                docker stop ${CONTAINER_NAME_STAGING} || true
                                docker rm ${CONTAINER_NAME_STAGING} || true
                                docker run -d --name ${CONTAINER_NAME_STAGING} -p ${HOST_PORT_STAGING}:${APPLICATION_PORT} ${FULL_IMAGE}
                                echo "✅ Deployed to Staging"
                            '
                        """
                    }
                }
            }
        }

        stage('Approve Production Deployment') {
            steps {
                input message: 'Approve deployment to Production?'
            }
        }

        stage('Deploy to Production') {
            when {
                expression { return currentBuild.result == null || currentBuild.result == 'SUCCESS' } // Chỉ chạy nếu build không thất bại
            }
            steps {
                input message: 'Proceed with deployment to Production?'
                withCredentials([sshUserPrivateKey(credentialsId: "${VPS_PRODUCTION_CREDENTIALS_ID}", host: "${VPS_PRODUCTION_HOST}")]) {
                    script {
                        echo "🚀 Deploying to Production (${VPS_PRODUCTION_HOST}:${HOST_PORT_PRODUCTION})..."
                        sh """
                            ssh -o StrictHostKeyChecking=no $SSH_USER@$SSH_HOST '
                                docker pull ${FULL_IMAGE}
                                docker stop ${CONTAINER_NAME_PRODUCTION} || true
                                docker rm ${CONTAINER_NAME_PRODUCTION} || true
                                docker run -d --name ${CONTAINER_NAME_PRODUCTION} -p ${HOST_PORT_PRODUCTION}:${APPLICATION_PORT} ${FULL_IMAGE}
                                echo "✅ Deployed to Production"
                            '
                        """
                    }
                }
            }
        }
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
