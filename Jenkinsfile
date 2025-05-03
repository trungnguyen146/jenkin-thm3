pipeline {
    agent any

    environment {
        GITHUB_CREDENTIALS = 'github-jenkins'
        DOCKER_CREDENTIALS = 'github-pat'
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Setup Buildx') {
            steps {
                script {
                    // Kiểm tra và cài đặt buildx nếu cần
                    sh '''
                    if ! docker buildx version; then
                        echo "buildx not found, attempting to install..."
                        docker buildx install || echo "buildx install failed, proceeding with default build"
                        docker buildx create --name mybuilder --use || echo "Failed to create builder, using default"
                    fi
                    docker buildx ls || echo "buildx ls failed"
                    '''
                }
            }
        }

        stage('Build') {
            steps {
                script {
                    // Chạy buildx với kiểm tra lỗi
                    sh '''
                    docker buildx build -t trungnguyen146/nginx:ver1 -f Dockerfile . --load || {
                        echo "buildx failed, falling back to docker build"
                        docker build -t trungnguyen146/nginx:ver1 -f Dockerfile .
                    }
                    '''
                }
            }
        }

        stage('Docker Login') {
            steps {
                script {
                    docker.withRegistry('https://index.docker.io/v1/', "${DOCKER_CREDENTIALS}") {
                        echo 'Đăng nhập Docker thành công!'
                    }
                }
            }
        }

        stage('Deploy') {
            steps {
                script {
                    docker.withRegistry('https://index.docker.io/v1/', "${DOCKER_CREDENTIALS}") {
                        def dockerImage = docker.image("trungnguyen146/nginx:ver1")
                        dockerImage.push()
                    }
                }
            }
        }
    }
}



// pipeline {
//     agent any

//     environment {
//         GITHUB_CREDENTIALS = 'github-jenkins'  // ID của GitHub credentials
//         DOCKER_CREDENTIALS = 'github-pat'  // ID của Docker credentials
//     }

//     stages {
//         stage('Checkout') {
//             steps {
//                 checkout scm  // Checkout the code from GitHub repository
//             }
//         }

//         stage('Build') {
//             steps {
//                 script {
//                     // Docker build step using Docker plugin
//                     docker.build("nginx:ver1", "-f Dockerfile .")
//                 }
//             }
//         }

//         stage('Docker Login') {
//             steps {
//                 script {
//                     // Docker login step using Docker credentials
//                     docker.withRegistry('', "${DOCKER_CREDENTIALS}") {
//                         echo 'Docker login successful!'
//                     }
//                 }
//             }
//         }

//         stage('Deploy') {
//             steps {
//                 script {
//                     docker.withRegistry('', "${DOCKER_CREDENTIALS}") {
//                         def dockerImage = docker.image("trungnguyen146/nginx:ver1")
//                         // Docker push step using Docker plugin
//                         docker.push()
//                     }
//                 }
//             }
//         }
//     }
// }



// # Testing 

// pipeline {
//     agent any
//     stages{
//         stage('Git clone'){
//             steps{
//                 git branch: 'main', url: 'https://github.com/trungnguyen146/jenkin-thm3.git'
//             }  
//         }

//         stage('Docker build image'){
//             steps{
//                 sh "docker build -t nginx:ver1 --force-rm -f Dockerfile ."
//             }  
//         }

//         stage('Build complete'){
//             steps{
//                 echo "Docker build complete"
//             }  
//         }
        
//     }
// }




// # Đoạn này để test kết nối
// pipeline {
//     agent any

//     stages {
//         stage('Checkout') {
//             steps {
//                 // Chỉ checkout code từ GitHub repository để kiểm tra kết nối
//                 checkout scm
//             }
//         }
//     }
// }
