#
# +----------------------------------------------------------------------+
# | PHP version 4.0                                                      |
# +----------------------------------------------------------------------+
# | Copyright (c) 1999 The PHP Group                                     |
# +----------------------------------------------------------------------+
# | This source file is subject to version 2.0 of the PHP license,       |
# | that is bundled with this package in the file LICENSE, and is        |
# | available at through the world-wide-web at                           |
# | http://www.php.net/license/2_0.txt.                                  |
# | If you did not receive a copy of the PHP license and are unable to   |
# | obtain it through the world-wide-web, please send a note to          |
# | license@php.net so we can mail you a copy immediately.               |
# +----------------------------------------------------------------------+
# | Authors: Stig Bakken <ssb@fast.no>                                   |
# |                                                                      |
# +----------------------------------------------------------------------+
#

TABLE_FILES=authors.sql mytable.sql #packages.sql
SQL_TYPE=mysql
SQL_USER=pear
SQL_PASSWORD=
SQL_SERVER=pear
SQL_COMMAND=mysql -u '$(SQL_USER)' '$(SQL_SERVER)' < $< > $@ 2>&1
SQL_PIPE_COMMAND=mysql -u '$(SQL_USER)' '$(SQL_SERVER)'
SQL_GRANT_FILE=

ECHO    = echo
TOUCH   = touch

MISC_FILES=$(SQL_GRANT_FILE)
SQL_FILES=$(TABLE_FILES) $(MISC_FILES)

TABLE_TARGETS=$(TABLE_FILES:.sql=.res)
MISC_TARGETS=$(MISC_FILES:.sql=.res)
SQL_TARGETS=$(TABLE_TARGETS) $(MISC_TARGETS)

CLEANFILES = *.res *.out *~

all: nothing

nothing:
	@$(ECHO)
	@$(ECHO) "   Make what?"
	@$(ECHO)
	@$(ECHO) "   'make create'   to set up database from scratch"
	@$(ECHO) "   'make destroy'  to destroy database"
	@$(ECHO)

create: $(SQL_TARGETS) prime
	@echo "Looking for errors..."
	@if grep -s Error *.res; then \
	    echo "Fix these errors, run 'make destroy' and try again." ; \
	else \
	    echo "No errors found." ; \
	fi

prime: data.res

destroy:
# Do "make drop" enough times to "solve" those who won't go away because
# of foreign keys to other tables that still exist.
	$(MAKE) -s drop

drop: clean
	cat $(SQL_FILES) | ./create2drop -$(SQL_TYPE) | $(SQL_PIPE_COMMAND) > drop.out

clean:
	-test -z "$(CLEANFILES)" || rm -f $(CLEANFILES)

test:
	@echo targets: $(SQL_TARGETS)

%.res: %.sql
	@echo -n "processing $<..."
	@$(SQL_COMMAND)
	@echo

# Specify dependencies between tables (though use of foreign keys)
# as dependencies between their respective .res files here.  For example,
# if the table "foo" has foreign keys to the table "bar", add
# "foo.res: bar.res".
