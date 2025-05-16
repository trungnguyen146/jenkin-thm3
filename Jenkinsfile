// Connection test - done
// pipeline {
//     agent {
//         docker {
//             image 'docker:dind'
//             args '--privileged'
//         }
//     }

//     environment {
//         DOCKERHUB_USERNAME = 'trungnguyen146'
//         IMAGE_NAME = 'trungnguyen146/php-website'
//         IMAGE_TAG = 'ver1'
//         FULL_IMAGE = "${IMAGE_NAME}:${IMAGE_TAG}"
//         DOCKERHUB_CREDENTIALS_ID = 'github-pat'

//         APPLICATION_PORT = 80

//         VPS_PRODUCTION_HOST = '14.225.219.14'
//         CONTAINER_NAME_PRODUCTION = 'php-container-prod'
//         HOST_PORT_PRODUCTION = 80
//         SSH_CREDENTIALS_ID = 'Prod_CredID'
//     }

//     stages {
//         stage('Test SSH Simple') {
//             steps {
//                 sshagent([env.SSH_CREDENTIALS_ID]) {
//                     sh "ssh -o StrictHostKeyChecking=no root@${env.VPS_PRODUCTION_HOST} 'echo \"SSH connection successful\"'"
//                 }
//             }
//         }
//     }

//     post {
//         always {
//             echo 'Clean up...'
//         }
//     }
// }

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
                // input message: "Proceed with deployment to Production?"
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

