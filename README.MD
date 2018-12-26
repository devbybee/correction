/*
THE SCENARIO
======================================================
- GET DATA FROM UTC CURRENT DATE AND MODIFIED DATE <= 30 MINUTES (IF THE SCEDULE RUNNING EACH 10 MINUTES, SPARE 20 MINUTES)
- PARAMETERS
--> PLAN DEPARTURE DATE (CURRENT)
--> MODIFIED DATE
*/

/*
NOTES
======================================================
MODIFIED_DATE(ODS) USE LOCAL TIME
rta detection not yet complete because any status from sabre
ideal time for next flight within 1 registration must greater than 10 mins because no case
*/