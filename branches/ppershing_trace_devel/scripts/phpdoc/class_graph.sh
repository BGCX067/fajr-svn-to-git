#!/bin/bash
TEMPLATE=`dirname $0`/template.xml

(
echo '<?xml version="1.0" encoding="ISO-8859-1"?>'
echo '<root>'
cat $1/classtrees*.html | \
  sed 'N;s/<h2>Root class Exception<\/h2>\n.*/<ul><li>Exception<ul>/;P;D' | \
  sed 's/\(Exception.*<\/a><\/li><\/ul>\)/\1<\/li><\/ul>/' | \
  sed 's/<a href="[^"]*">//g' | sed 's/<\/a>//g' | \
  sed 's/<\/li>/<\/li>\n/g' | grep -E '<ul>|<li>|</li>|</ul>'

echo '</root>' ) > $2/graph.xml

xsltproc $TEMPLATE $2/graph.xml > $2/graph2.xml

cat $2/graph2.xml | sed 's/<pair>//' | sed 's/<\/pair>//' | grep -v -E '^$' | \
  sed 's/<zoznam>/digraph G {/' | sed 's/<\/zoznam>/}/' | \
  sed 's/<?xml.*//' | \
  sed 's/ (implements )/_impl/' | \
  sed 's/ (implements \(.*\))/_impl_\1/' | \
  sed 's/^,\(.*\)/\1 [shape=box# fillcolor=yellow# style="filled"];/' | \
  sed 's/\(.*\),\(.*\)/\1 -> \2 [len = 2.5]; \1 [style="filled"# fillcolor=lawngreen]; \2 \
  [style="filled"# fillcolor=lightskyblue];/' |

  tr '#' ',' > $2/class_graph.dot

neato $2/class_graph.dot -Tpng > $2/class_graph.png
rm $2/graph.xml
rm $2/graph2.xml
