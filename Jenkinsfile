pipeline {
    agent any
    
    environment {
        GITHUB_CREDENTIALS = 'github-jenkins'  // ID của GitHub credentials
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
                // Your build steps here (Docker build, etc.)
                sh 'docker build -t nginx:ver1 --force-rm -f Dockerfile .'
            }
        }

        stage('Deploy') {
            steps {
                // Your deploy steps here
                sh 'docker push nginx:ver1'
            }
        }
    }
}
