BASEDIR=`dirname "$0"`
HOME=`dirname "$BASEDIR"`
xmllint --format --noout $HOME/xml/*.xml  2> $BASEDIR/merveilles17_malformations.txt

