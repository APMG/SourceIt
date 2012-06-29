all:
	echo "You likely want 'make db-install'"

install: db-install js-replace js-minify
db-install: db-create db-current
min-deploy:  js-html-replace js-minify
deploy: db-current js-html-replace js-minify
test: check

check: t/000-sanity.t
	prove -r t

db-create:
	mysql -u root -p < etc/ifdb.sql

db-current:
	php etc/migrate.php

db-version:
	php etc/migrate.php $(num)

js-html-replace:
	php bin/process.php

js-minify:
	bin/minify.sh

