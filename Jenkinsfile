

pipeline {
    agent any

    environment {
        GITHUB_CREDENTIALS = 'github-jenkins'  // ID của GitHub credentials
        DOCKER_USERNAME = 'trungnguyen1462k@gmail.com'  // Docker Hub username
        DOCKER_PASSWORD = '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'  // Docker Hub password
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
                // Docker login using shell command
                sh '''
                echo $DOCKER_PASSWORD | docker login -u $DOCKER_USERNAME --password-stdin
                '''
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
