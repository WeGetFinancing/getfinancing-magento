MODULE=magento-getfinancing
VERSION=1.7.0

PACKAGE=$(MODULE)-$(VERSION)

all: $(PACKAGE).zip

gitversion: app lib Makefile .
	git describe > gitversion

$(PACKAGE).zip: Makefile app lib modman gitversion
	-rm $(PACKAGE).zip
	mkdir -p $(PACKAGE)
	rsync -arv app lib modman README gitversion $(PACKAGE)
	find $(PACKAGE) -name '*.swp' -exec rm {} \;
	zip -r $(PACKAGE).zip $(PACKAGE)
	rm -rf $(PACKAGE)
