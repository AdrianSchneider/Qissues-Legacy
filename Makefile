PROJECT = "Qissues"

all: ;@echo "Compiling ${PROJECT}"; \
	wget -nc https://getcomposer.org/composer.phar; \
	php composer.phar install; \
	bin/compile; \
	chmod +x qissues.phar; \
	echo "Created qissues.phar";

install: ;@echo "Installing ${PROJECT}"; \
	mkdir -p ~/bin; \
	mv qissues.phar ~/bin/qissues -f;

test: ;@echo "Unit Testing ${PROJECT}"; \
	bin/phpunit;

travis:
	composer install;

coverage: ;@echo "Generating unit test coverage for ${PROJECT}"; \
	bin/phpunit --coverage-html=coverage;

.PHONY: all install test coverage travis
