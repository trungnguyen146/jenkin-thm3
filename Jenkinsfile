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
