# Minimal makefile for Sphinx documentation
#

# You can set these variables from the command line, and also
# from the environment for the first two.
SPHINXOPTS    ?= -W
SPHINXBUILD   ?= sphinx-build
SOURCEDIR     = doc
BUILDDIR      = build

.PHONY: help sphinx

build:
	mkdir build

build/vimdoc:
	git clone https://github.com/google/vimdoc build/vimdoc

build/vimdoc/build: build/vimdoc
	cd build/vimdoc; python3 setup.py config
	cd build/vimdoc; python3 setup.py build 

build/bin/vimdoc: build/vimdoc/build
	cd build/vimdoc; python3 setup.py install --user

vimdoc: build/bin/vimdoc
	vimdoc .

configreference:
	./bin/phpactor development:configuration-reference > doc/reference/configuration.rst

# Put it first so that "make" without argument is like "make help".
help:
	@$(SPHINXBUILD) -M help "$(SOURCEDIR)" "$(BUILDDIR)" $(SPHINXOPTS) $(O)

sphinxwatch:
	@config/bin/watchdocs.sh

sphinx:
	@$(SPHINXBUILD) -M html "$(SOURCEDIR)" "$(BUILDDIR)" $(SPHINXOPTS) $(O)

sphinxlatex:
	@$(SPHINXBUILD) -M latex "$(SOURCEDIR)" "$(BUILDDIR)" $(SPHINXOPTS) $(O)

docs: configreference sphinx vimdoc

clean:
	rm -Rf build
