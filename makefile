#
# Makefile
#

.PHONY: help
.DEFAULT_GOAL := help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

# ------------------------------------------------------------------------------------------------------------

prepare-for-store: ## Preparing package for store
	rm -rf ElioDataDiscovery
	mkdir ElioDataDiscovery && mkdir ElioDataDiscovery/src
	cp -r src ElioDataDiscovery
	cp composer.json ElioDataDiscovery
	cp README.md ElioDataDiscovery
	cp phpstan.neon ElioDataDiscovery
	zip ElioDataDiscovery.zip ElioDataDiscovery -r
	rm -rf ElioDataDiscovery