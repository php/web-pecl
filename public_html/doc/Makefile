#HTML_STYLESHEET=/usr/lib/sgml/stylesheets/nwalsh-modular/html/docbook.dsl
HTML_STYLESHEET=/home/ssb/cvs/php/phpdoc/html.dsl
PRINT_STYLESHEET=/usr/lib/sgml/stylesheets/nwalsh-modular/print/docbook.dsl
XML_DCL=/home/ssb/cvs/php/phpdoc/phpdocxml.dcl

all: pear.html

pear.html: pear.xml $(HTML_STYLESHEET) Makefile
	-rm -f $@
#	jade -d $(HTML_STYLESHEET) -V nochunks -t sgml $(XML_DCL) $< | tidy -latin1 > pear.html
	jade -d $(HTML_STYLESHEET) -V nochunks -t sgml $(XML_DCL) $< > pear.html
