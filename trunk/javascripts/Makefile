CC=java -jar ../third_party/closure-compiler/compiler.jar --warning_level VERBOSE
DEPCALC=../scripts/jsdeps.sh
TP=--third_party=src/third_party/tablesort.js \
   --third_party=src/third_party/prototype.js \
   --third_party=src/third_party/ga.js \
   --third_party=src/third_party/goog.base.js

.PHONY: deploy
deploy: out/fajr.concat.js out/fajr.compiled.js
	# "Copying $^ to ../web/javascripts"
	cp $^ ../web/javascripts/

out/fajr.compiled.js: src/*
	## "Optimizing $^"
	SOURCES=`$(DEPCALC) -i src/fajr.js |  sed 's/^/--js /'`&& \
		echo -e "Compiling sources:\n$$SOURCES"&& \
		$(CC) $(TP) $$SOURCES --js_output_file $@

out/fajr.concat.js: src/*
	$(DEPCALC) -i src/fajr.js | xargs cat > $@

.PHONY: out/test_deps.js

out/test_deps.js:
	SOURCES=`find ../tests/web -name '*Test.js' -type f | \
			$(DEPCALC) |  \
			grep -v 'Test\.js' | sed 's/^/--js /'`&& \
		echo -e "Compiling sources:\n$$SOURCES"&& \
		$(CC) $(TP) $$SOURCES --js_output_file $@
