FROM nginx:1.31-alpine

COPY ./docker/common/nginx/snippets /etc/nginx/snippets
COPY ./docker/production/nginx/conf.d /etc/nginx/conf.d

WORKDIR /app
