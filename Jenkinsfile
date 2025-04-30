pipeline {
    agent any
    stages{
        stage('Git clone'){
            steps{
                git branch: 'main', url: 'https://github.com/trungnguyen146/jenkin-thm3.git'
            }  
        }

        stage('Docker build image'){
            steps{
                sh "docker build -t nginx:ver1 --force-rm -f Dockerfile ."
            }  
        }

        stage('Build complete'){
            steps{
                echo "Docker build complete"
            }  
        }
        
    }
}


// pipeline {
//     agent any
    
//     environment {
//         GITHUB_CREDENTIALS = 'github-jenkins'  // ID của GitHub credentials
        
//     }

//     stages {
//         stage('Checkout') {
//             steps {
//                 // Checkout the code from GitHub repository
//                 checkout scm
//             }
//         }

//         stage('Build') {
//             steps {
//                 // Docker build step
//                 sh 'docker build -t nginx:ver1 --force-rm -f Dockerfile .'
//             }
//         }

//         stage('Docker Login') {
//             steps {
//                 script {
//                     // Docker login step
//                     docker.withRegistry('', "${GITHUB_CREDENTIALS}") {
//                         echo 'Docker login successful!'
//                     }
//                 }
//             }
//         }

//         stage('Deploy') {
//             steps {
//                 // Docker push step
//                 script {
//                     docker.withRegistry('', "${GITHUB_CREDENTIALS}") {
//                         sh 'docker push nginx:ver1'
//                     }
//                 }
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
