FROM ubuntu:20.04
RUN apt update -y && apt install -y nginx
WORKDIR /etc/nginx
CMD ["nginx", "-g", "daemon off;"]




# FROM ubuntu:20.04
# RUN apt update -y
# RUN apt install -y nginx systemctl
# WORKDIR /etc/nginx
