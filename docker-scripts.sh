#!/bin/bash
# Helper scripts untuk Docker Nginx setup

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Functions
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}! $1${NC}"
}

# Setup SSL (self-signed untuk development)
setup_ssl_dev() {
    echo "Setting up self-signed SSL certificate for development..."
    
    if [ -f "docker/nginx/ssl/certificate.crt" ]; then
        print_warning "SSL certificate already exists. Skipping..."
        return
    fi
    
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout docker/nginx/ssl/private.key \
        -out docker/nginx/ssl/certificate.crt \
        -subj "/C=ID/ST=Jakarta/L=Jakarta/O=Videa/OU=IT/CN=sps.videaclass.com"
    
    print_success "Self-signed SSL certificate generated"
}

# Check prerequisites
check_prereqs() {
    echo "Checking prerequisites..."
    
    # Check docker
    if ! command -v docker &> /dev/null; then
        print_error "Docker is not installed"
        exit 1
    fi
    print_success "Docker is installed"
    
    # Check docker-compose
    if ! command -v docker-compose &> /dev/null; then
        print_error "Docker Compose is not installed"
        exit 1
    fi
    print_success "Docker Compose is installed"
    
    # Check SSL certificate
    if [ ! -f "docker/nginx/ssl/certificate.crt" ]; then
        print_warning "SSL certificate not found"
        read -p "Generate self-signed certificate for development? (y/n) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            setup_ssl_dev
        fi
    else
        print_success "SSL certificate exists"
    fi
}

# Build and start
start() {
    echo "Starting Docker containers..."
    check_prereqs
    docker-compose up -d --build
    print_success "Containers started"
    docker-compose ps
}

# Stop containers
stop() {
    echo "Stopping Docker containers..."
    docker-compose down
    print_success "Containers stopped"
}

# Restart specific service
restart() {
    service=${1:-nginx}
    echo "Restarting $service..."
    docker-compose restart $service
    print_success "$service restarted"
}

# View logs
logs() {
    service=${1:-}
    if [ -z "$service" ]; then
        docker-compose logs -f
    else
        docker-compose logs -f $service
    fi
}

# Health check
health_check() {
    echo "Running health check..."
    echo ""
    
    echo "=== Container Status ==="
    docker-compose ps
    echo ""
    
    echo "=== Network Test ==="
    if docker exec nginx_proxy wget -q -O- http://frankenphp_app:8000 >/dev/null 2>&1; then
        print_success "Nginx → FrankenPHP: OK"
    else
        print_error "Nginx → FrankenPHP: FAILED"
    fi
    echo ""
    
    echo "=== Nginx Config Test ==="
    if docker exec nginx_proxy nginx -t 2>&1; then
        print_success "Nginx config: OK"
    else
        print_error "Nginx config: FAILED"
    fi
    echo ""
    
    echo "=== SSL Certificate ==="
    if openssl x509 -in docker/nginx/ssl/certificate.crt -noout -dates 2>/dev/null; then
        print_success "Certificate: OK"
    else
        print_error "Certificate: NOT FOUND"
    fi
    echo ""
    
    echo "=== Port Listening ==="
    if command -v netstat &> /dev/null; then
        netstat -tulpn 2>/dev/null | grep -E ':(80|443) ' || ss -tulpn | grep -E ':(80|443) '
    else
        ss -tulpn | grep -E ':(80|443) '
    fi
}

# Reload nginx config
reload_nginx() {
    echo "Reloading Nginx configuration..."
    docker exec nginx_proxy nginx -t
    docker exec nginx_proxy nginx -s reload
    print_success "Nginx configuration reloaded"
}

# Show usage
usage() {
    cat << EOF
Docker Nginx Helper Scripts

Usage: ./docker-scripts.sh [command]

Commands:
    start           Build and start all containers
    stop            Stop all containers
    restart [svc]   Restart service (default: nginx)
    logs [svc]      View logs (default: all services)
    health          Run health check
    reload          Reload nginx configuration
    ssl-dev         Generate self-signed SSL for development
    
Examples:
    ./docker-scripts.sh start
    ./docker-scripts.sh logs nginx
    ./docker-scripts.sh restart frankenphp
    ./docker-scripts.sh health

EOF
}

# Main
case ${1:-} in
    start)
        start
        ;;
    stop)
        stop
        ;;
    restart)
        restart $2
        ;;
    logs)
        logs $2
        ;;
    health)
        health_check
        ;;
    reload)
        reload_nginx
        ;;
    ssl-dev)
        setup_ssl_dev
        ;;
    *)
        usage
        ;;
esac
