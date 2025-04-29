FROM ubuntu:20.04
RUN apt update -y
RUN apt install -y nginx systemctl
WORKDIR /etc/nginx
