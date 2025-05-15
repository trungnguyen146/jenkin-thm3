pipeline {
    agent any

    environment {
        VPS_PRODUCTION_HOST = '14.225.219.14'      // Thay bằng IP hoặc hostname VPS của bạn
        SSH_CREDENTIALS_ID = 'Prod_CredID'         // Thay bằng ID credential SSH key của bạn
        SSH_USERNAME = 'root'                     // Thay bằng username SSH của bạn (nếu cần)
    }

stages {
    stage('Test SSH Connection with Key (Manual)') {
        steps {
            script {
                def SSH_HOST = "${VPS_PRODUCTION_HOST}"
                def SSH_USER = "${env.SSH_USERNAME}" // Sử dụng biến môi trường

                withCredentials([sshUserPrivateKey(credentialsId: "${SSH_CREDENTIALS_ID}", keyFileVariable: 'TEMP_SSH_KEY')]) {
                    sh """
                        echo "🩺 Testing SSH connection to ${SSH_USER}@${SSH_HOST} using SSH key (manual)..."
                        chmod 400 "\$TEMP_SSH_KEY" // TEMP_SSH_KEY là biến môi trường do withCredentials cung cấp, nên \$TEMP_SSH_KEY là đúng
                        ssh -o StrictHostKeyChecking=no -i "\$TEMP_SSH_KEY" "${SSH_USER}@${SSH_HOST}" -p 22 -o ConnectTimeout=10 'echo Connected successfully'
                    """
                }
            }
        }
    }
}








