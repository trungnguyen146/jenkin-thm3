pipeline {
    agent {
        docker {
            image 'docker:dind'
            args '-v /var/run/docker.sock:/var/run/docker.sock --privileged'
        }
    }

    environment {
        DOCKERHUB_USERNAME = 'trungnguyen146' 
        IMAGE_NAME_PREFIX = "${DOCKERHUB_USERNAME}/php-nginx-app"
        DOCKER_CREDENTIALS_ID = 'github-pat' // docker hub cred 

        // Stage_CredID
        VPS_STAGING_CREDENTIALS_ID = 'Stag_CredID' 
        VPS_STAGING_HOST = '14.225.219.164' 

        // Prod_CredID
        VPS_PRODUCTION_CREDENTIALS_ID = 'your-vps-production-ssh-credentials-id' // Thay bằng ID SSH credential cho VPS Production
        VPS_PRODUCTION_HOST = 'your-vps-production-ip' 
        
        // Name
        CONTAINER_NAME_STAGING = 'php-nginx-staging'
        CONTAINER_NAME_PRODUCTION = 'php-nginx-prod'

        //Port 
        APPLICATION_PORT = 80 
        HOST_PORT_STAGING = 8888 
        HOST_PORT_PRODUCTION = 80 
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Set Build Version') {
            steps {
                script {
                    // Lấy số build Jenkins làm một phần của version
                    BUILD_VERSION = "1.0.${BUILD_NUMBER}"
                    DOCKER_IMAGE_TAGGED = "${env.IMAGE_NAME_PREFIX}:${BUILD_VERSION}"
                    echo "Building Docker image with tag: ${DOCKER_IMAGE_TAGGED}"
                }
            }
        }

        stage('Build and Push Docker Image') {
            steps {
                script {
                    sh "docker build -t ${DOCKER_IMAGE_TAGGED} -f Dockerfile ."
                    docker.withRegistry('https://index.docker.io/v1/', "${DOCKER_CREDENTIALS_ID}") {
                        docker.image("${DOCKER_IMAGE_TAGGED}").push()
                        echo "Pushed ${DOCKER_IMAGE_TAGGED} to Docker Hub"
                    }
                }
            }
        }

        stage('Deploy to Staging') {
            steps {
                withCredentials([sshUserPrivateKey(credentialsId: "${VPS_STAGING_CREDENTIALS_ID}", host: "${VPS_STAGING_HOST}")]) {
                    script {
                        echo "Deploying ${DOCKER_IMAGE_TAGGED} to VPS Staging (${VPS_STAGING_HOST}:${HOST_PORT_STAGING})..."
                        sh """
                            ssh -o StrictHostKeyChecking=no $SSH_USER@$SSH_HOST '
                                docker pull ${DOCKER_IMAGE_TAGGED}
                                docker stop ${CONTAINER_NAME_STAGING} || true
                                docker rm ${CONTAINER_NAME_STAGING} || true
                                docker run -d --name ${CONTAINER_NAME_STAGING} -p ${HOST_PORT_STAGING}:${APPLICATION_PORT} ${DOCKER_IMAGE_TAGGED}
                                echo "Deployment to Staging complete."
                            '
                        """
                    }
                }
            }
        }

        stage('Kiểm thử Staging (Manual)') {
            steps {
                input message: 'Approve to proceed to Production after testing Staging?'
                script {
                    echo 'Please perform testing on the Staging environment now.'
                    echo "Application should be available at http://${env.VPS_STAGING_HOST}:${env.HOST_PORT_STAGING}"
                }
            }
        }

        stage('Deploy to Production') {
            when {
                input message: 'Approve deployment to Production?'
            }
            steps {
                withCredentials([sshUserPrivateKey(credentialsId: "${VPS_PRODUCTION_CREDENTIALS_ID}", host: "${VPS_PRODUCTION_HOST}")]) {
                    script {
                        echo "Deploying ${DOCKER_IMAGE_TAGGED} to VPS Production (${VPS_PRODUCTION_HOST}:${HOST_PORT_PRODUCTION})..."
                        sh """
                            ssh -o StrictHostKeyChecking=no $SSH_USER@$SSH_HOST '
                                docker pull ${DOCKER_IMAGE_TAGGED}
                                docker stop ${CONTAINER_NAME_PRODUCTION} || true
                                docker rm ${CONTAINER_NAME_PRODUCTION} || true
                                docker run -d --name ${CONTAINER_NAME_PRODUCTION} -p ${HOST_PORT_PRODUCTION}:${APPLICATION_PORT} ${DOCKER_IMAGE_TAGGED}
                                echo "Deployment to Production complete."
                            '
                        """
                    }
                }
            }
        }
    }
}
