# Simple Makefile to do an automated test using docker-compose
all: stop build start test stop

# Build both nosqlmap and vulnerable application containers
build:
	@echo "Building NoSQLMap Docker image..."
	docker-compose build
	@echo "Building vulnerable application..."
	cd vuln_apps && docker-compose build

NOSQLMAP_VULN_APPS_APACHE_PORT = 8080
export NOSQLMAP_VULN_APPS_APACHE_PORT

# Start vulnerable application and initialize database
start:
	@echo "Starting vulnerable application..."
	cd vuln_apps && docker-compose up -d
	@echo "Initializing test database with sample data..."
	@curl -sS http://localhost:$(NOSQLMAP_VULN_APPS_APACHE_PORT)/populate_db.php || (echo "❌ Database initialization failed" && exit 1)
	@echo ""
	@curl -sS "http://localhost:$(NOSQLMAP_VULN_APPS_APACHE_PORT)/acct.php?acctid=1" | grep -q "Robin" || (echo "❌ Database verification failed" && exit 1)
	@echo "Vulnerable app should be available at http://localhost:$(NOSQLMAP_VULN_APPS_APACHE_PORT)/"

# Test 1: Account Lookup (acct.php)
test-acct:
	docker-compose run --rm nosqlmap \
		--attack 2 \
		--victim host.docker.internal \
		--webPort $(NOSQLMAP_VULN_APPS_APACHE_PORT) \
		--uri "/acct.php?acctid=test" \
		--httpMethod GET \
		--params 1 \
		--injectSize 4 \
		--injectFormat 2 \
		--doTimeAttack n

# Test 2: User Data Lookup (userdata.php)
test-userdata:
	docker-compose run --rm nosqlmap \
		--attack 2 \
		--victim host.docker.internal \
		--webPort $(NOSQLMAP_VULN_APPS_APACHE_PORT) \
		--uri "/userdata.php?usersearch=test" \
		--httpMethod GET \
		--params 1 \
		--injectSize 4 \
		--injectFormat 2 \
		--doTimeAttack n

# Test 3: Order Data Lookup (orderdata.php)
test-orderdata:
	docker-compose run --rm nosqlmap \
		--attack 2 \
		--victim host.docker.internal \
		--webPort $(NOSQLMAP_VULN_APPS_APACHE_PORT) \
		--uri "/orderdata.php?ordersearch=test" \
		--httpMethod GET \
		--params 1 \
		--injectSize 4 \
		--injectFormat 2 \
		--doTimeAttack n

# Run all tests (assumes you've already done build and start)
test: test-acct test-userdata test-orderdata

stop:
	cd vuln_apps && docker-compose down -v || true
	docker-compose down --remove-orphans || true

help:
	@echo "Available targets:"
	@echo "  all            - build, start, test, stop"
	@echo "  build          - Build all containers"
	@echo "  start          - Start and initialize vulnerable application"
	@echo "  test           - Run all tests"
	@echo "  stop           - Stop and clean up all containers"
