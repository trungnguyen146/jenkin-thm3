

pipeline {
    agent any

    environment {
        GITHUB_CREDENTIALS = 'github-jenkins'  // ID của GitHub credentials trong Jenkins
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm  // Checkout the code from GitHub repository
            }
        }

        stage('Build') {
            steps {
                // Docker build step using shell command
                sh 'docker build -t nginx:ver1 --force-rm -f Dockerfile .'
            }
        }

        stage('Docker Login') {
            steps {
                script {
                    // Docker login using GitHub PAT stored in Jenkins credentials
                    docker.withRegistry('', 'github-jenkins') {
                        echo 'Docker login successful!'
                    }
                }
            }
        }

        stage('Deploy') {
            steps {
                // Docker push step using shell command
                sh 'docker push nginx:ver1'
            }
        }
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
