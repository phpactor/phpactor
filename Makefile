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
	vimdoc .

configreference:
	./bin/phpactor development:generate-documentation extension > doc/reference/configuration.rst
	./bin/phpactor development:generate-documentation rpc > doc/reference/rpc_command.rst
	./bin/phpactor development:generate-documentation diagnostic > doc/reference/diagnostic.rst

# Put it first so that "make" without argument is like "make help".
help:
	@$(SPHINXBUILD) -M help "$(SOURCEDIR)" "$(BUILDDIR)" $(SPHINXOPTS) $(O)

sphinxwatch:
	@$(SPHINXAUTOBUILD) "$(SOURCEDIR)" "$(BUILDDIR)" $(SPHINXOPTS) $(O)

sphinx:
	@$(SPHINXBUILD) -M html "$(SOURCEDIR)" "$(BUILDDIR)" $(SPHINXOPTS) $(O)

sphinxlatex:
	@$(SPHINXBUILD) -M latex "$(SOURCEDIR)" "$(BUILDDIR)" $(SPHINXOPTS) $(O)

docs: configreference sphinx vimdoc

clean:
	rm -Rf build
