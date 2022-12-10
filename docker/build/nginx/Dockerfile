ARG VERSION
FROM nginx:1.22.1

RUN apt-get update && apt-get install -y curl

COPY app.conf /etc/nginx/conf.d/default.conf
