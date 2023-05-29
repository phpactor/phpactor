# Minimal makefile for Sphinx documentation
#

# You can set these variables from the command line, and also
# from the environment for the first two.
SPHINXOPTS       ?= -W
SPHINXBUILD      ?= sphinx-build
SPHINXAUTOBUILD  ?= sphinx-autobuild
SOURCEDIR         = doc
BUILDDIR          = build

.PHONY: help sphinx

build:
	mkdir build

vimdoc:
	docker compose run php vimdoc .

configreference:
	docker compose run php ./bin/phpactor development:generate-documentation extension > doc/reference/configuration.rst
	docker compose run php ./bin/phpactor development:generate-documentation rpc > doc/reference/rpc_command.rst
	docker compose run php ./bin/phpactor development:generate-documentation diagnostic > doc/reference/diagnostic.rst

# Put it first so that "make" without argument is like "make help".
help:
	docker compose run php $(SPHINXBUILD) -M help "$(SOURCEDIR)" "$(BUILDDIR)" $(SPHINXOPTS) $(O)

sphinxwatch:
	docker compose run php $(SPHINXAUTOBUILD) "$(SOURCEDIR)" "$(BUILDDIR)" $(SPHINXOPTS) $(O)

sphinx:
	docker compose run php $(SPHINXBUILD) -M html "$(SOURCEDIR)" "$(BUILDDIR)" $(SPHINXOPTS) $(O)

sphinxlatex:
	docker compose run php $(SPHINXBUILD) -M latex "$(SOURCEDIR)" "$(BUILDDIR)" $(SPHINXOPTS) $(O)

docs: configreference vimdoc sphinx

clean:
	rm -Rf build
