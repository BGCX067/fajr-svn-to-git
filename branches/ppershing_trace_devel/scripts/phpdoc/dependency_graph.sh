#!/bin/bash
REPORT=`readlink -f $1/report`;
CLUSTER_PY=`readlink -f $(dirname $0)`/dependency_clusters.py;
cd $1
ZOZNAM=$(find * | grep '\.php' | grep -v '\.svn' | grep -v 'report' | \
    grep -v '\.swp' | LC_ALL='C' grep '[A-Z]' | grep -v 'Test' | \
    grep -v 'scripts' )


OUT=$REPORT/class_dependency_graph.dot;

echo "
digraph g {
concentrate=true;
rankdir=\"LR\";
clusterrank=\"local\";
ranksep=\"3.0\";
nodesep=\"0.5\";
node[shape=box, fontsize=14, fillcolor=darkolivegreen1, style=filled];
" > $OUT;

echo $ZOZNAM | $CLUSTER_PY >> $OUT;
for file in $ZOZNAM; do
  CLASS=`basename $file | sed 's/\.php//'`
  VYSKYTY=`grep -l "[^a-zA-Z]$CLASS[^a-zA-Z]" $ZOZNAM`;
  for vyskyt in $VYSKYTY; do
    CLASS2=`basename $vyskyt | sed 's/\.php//'`
    if [ $CLASS != $CLASS2 ] ; then
      echo "$CLASS2 -> $CLASS;" >> $OUT
    fi
  done
done
echo "}" >> $OUT
dot $OUT -Tpng > ${OUT%%.dot}.png
