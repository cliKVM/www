FROM nginx:alpine

LABEL maintainer="MoleKVM Team"
LABEL description="Plesk Panel Emulation for Docker Development"

# Install basic tools for health checks
RUN apk add --no-cache curl

# Copy Plesk mock UI files
COPY index.html /usr/share/nginx/html/
COPY css/ /usr/share/nginx/html/css/
COPY js/ /usr/share/nginx/html/js/

# Copy nginx config for Plesk-like setup
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Set permissions
RUN chown -R nginx:nginx /usr/share/nginx/html

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
