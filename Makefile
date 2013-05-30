MODULE=magento-getfinancing
VERSION=1.5.0

PACKAGE=$(MODULE)-$(VERSION)

all: $(PACKAGE).zip

$(PACKAGE).zip: Makefile app lib modman
	-rm $(PACKAGE).zip
	mkdir -p $(PACKAGE)
	rsync -arv app lib modman README $(PACKAGE)
	find $(PACKAGE) -name '*.swp' -exec rm {} \;
	zip -r $(PACKAGE).zip $(PACKAGE)
	rm -rf $(PACKAGE)
