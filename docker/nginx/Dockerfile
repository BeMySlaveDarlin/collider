FROM nginx:alpine

RUN apk add --no-cache gettext

RUN mkdir /etc/nginx/templates
COPY default.conf.template /etc/nginx/templates
