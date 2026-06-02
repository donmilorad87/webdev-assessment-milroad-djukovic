# Nginx image — the only service exposed to the host. Reverse-proxies PHP to PHP-FPM.
FROM nginx:1.27-alpine

# Replace the default site config with ours.
RUN rm /etc/nginx/conf.d/default.conf
COPY default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
