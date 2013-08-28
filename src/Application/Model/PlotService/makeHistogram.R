# usage:
# R --vanilla --args "/data/myHistogram.png" "1,2,3,4"

makeHistogram = function(filename, lifespans) {
    png(filename=filename, width=250, height=150, units="px", pointsize=16, bg="white" );
    par(mar=c(3,3,0.5,1)); # trim margin around plot [b,l,t,r]
    par(tcl=0.35); # switch tick marks to insides of axes
    par(mgp=c(1.5,0.2,0)); # set margin lines; default c(3,1,0) [title,labels,line]
    par(xaxs="r", yaxs="r"); # extend axis limits
    par(lwd=2); # line width 2px

    hist(lifespans, breaks=10, lty=1, lwd=2, xlab="age (cell divisions)", ylab="count", main="");
    rug(lifespans, ticksize="0.1", lwd="1", side=1, col="red");
    par(new=T);
    boxplot(lifespans, horizontal=TRUE, axes=FALSE, col="red", xlim=c(1,6), at=3);
    par(new=F);
    dev.off();
}

args = commandArgs(TRUE);
filename = args[1];
lifespans = lapply(strsplit(args[2], ','), as.numeric)[[1]];
makeHistogram(filename, lifespans);
