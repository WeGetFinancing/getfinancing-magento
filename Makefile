MODULE=magento-credex

all: $(MODULE).zip

$(MODULE).zip: Makefile app lib modman
	-rm $(MODULE).zip
	mkdir -p $(MODULE)
	rsync -arv app lib modman README $(MODULE)
	zip -r $(MODULE).zip $(MODULE)
	rm -rf $(MODULE)
