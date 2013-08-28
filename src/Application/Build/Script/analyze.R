#!/bin/R

library(DBI);
library(RMySQL);
library(exactRankTests);

args = commandArgs(trailingOnly=TRUE);
database = args[1];
username = args[2];
password = args[3];
connection = dbConnect("MySQL", host="localhost", dbname=database, username, password);

rows = dbGetQuery(connection, "
    SELECT
        comparison.id as id,
        test_sample.lifespans as test_lifespans,
        reference_sample.lifespans as reference_lifespans
    FROM comparison
    LEFT JOIN sample test_sample ON test_sample.id = comparison.test_sample_id
    LEFT JOIN sample reference_sample ON reference_sample.id = comparison.reference_sample_id
    WHERE test_sample.lifespans IS NOT NULL
        AND reference_sample.lifespans IS NOT NULL
");

updateRow = function(row, connection) {
    id = as.numeric(row["id"]);
    testLifespans = sapply(strsplit(row["test_lifespans"], ","), as.numeric);
    refLifespans = sapply(strsplit(row["reference_lifespans"], ","), as.numeric);

    # calculate ranksum test
    testMean = mean(testLifespans);
    refMean = mean(refLifespans);
    percentChange = (testMean - refMean) / refMean * 100;

    # calculate ranksum test
    stats = wilcox.exact(testLifespans, refLifespans);
    ranksumU = stats$statistic;
    ranksumP = stats$p.value;

    # save computations to database
    sql = sprintf("
        UPDATE comparison SET
            percent_change = '%e',
            ranksum_u = '%e',
            ranksum_p = '%.12e'
        WHERE id = '%d'
        ", percentChange, ranksumU, ranksumP, id
    );
    dbSendQuery(connection, sql);
}

if (length(rows) > 0) {
    #dbSendQuery(connection, "START TRANSACTION");
    #dbBeginTransaction(connection);
    apply(rows, 1, updateRow, connection=connection);
    #dbCommit(connection);
}

dbDisconnect(connection);
