SERVER=selenium-server-standalone-2.32.0.jar
URL="http://magento17.localhost/"
RESULT="result.html"

test:
	java -jar $(SERVER) -htmlSuite "*firefox" $(URL) `pwd`/testsuite-magento.html $(RESULT) -port 5555

