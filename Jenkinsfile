

pipeline {
    agent any
    
    environment {
        GITHUB_CREDENTIALS = 'github-jenkins'  // ID của GitHub credentials
        DOCKER_CREDENTIALS = 'docker-credentials'  // ID của Docker credentials
    }

    stages {
        stage('Checkout') {
            steps {
                // Checkout the code from GitHub repository
                checkout scm
            }
        }

        stage('Build') {
            steps {
                // Docker build step
                script {
                    def customImage = docker.build("nginx:ver1", "-f Dockerfile .")
                }
            }
        }

        // stage('Docker Login') {
        //     steps {
        //         script {
        //             // Docker login step
        //             docker.withRegistry('', "${DOCKER_CREDENTIALS}") {
        //                 echo 'Docker login successful!'
        //             }
        //         }
        //     }
        // }

        // stage('Deploy') {
        //     steps {
        //         script {
        //             docker.withRegistry('', "${DOCKER_CREDENTIALS}") {
        //                 // Docker push step
        //                 docker.push('nginx:ver1')
        //             }
        //         }
        //     }
        // }
    }
}




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
