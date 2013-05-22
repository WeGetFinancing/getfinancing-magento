MODULE=magento-getfinancing

all: $(MODULE).zip

$(MODULE).zip: Makefile app lib modman
	-rm $(MODULE).zip
	mkdir -p $(MODULE)
	rsync -arv app lib modman README $(MODULE)
	find ($MODULE) -name '*.swp' -exec rm {} \;
	zip -r $(MODULE).zip $(MODULE)
	rm -rf $(MODULE)
