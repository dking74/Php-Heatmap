<?php

// String template for group availability by minute
$group_availability_per_minute = <<<XML
SELECT
    c.Name,
    CONCAT(Month(Tolocal(c.ContainerStatus.DateTime)), '-', Day(Tolocal(c.ContainerStatus.DateTime)), '-', Hour(Tolocal(c.ContainerStatus.DateTime)), '-', Minute(Tolocal(c.ContainerStatus.DateTime))) as Date,
    c.ContainerStatus.PercentAvailability * c.ContainerStatus.Weight / c.ContainerStatus.Weight as Availability
FROM Orion.Container c
WHERE
    MonthDiff(Tolocal(c.ContainerStatus.DateTime), GetDate()) = 1 AND
    c.Name = '{GROUP}'
ORDER BY Tolocal(c.ContainerStatus.DateTime)
XML;

// String template for group availability computed for the month
$group_availability_per_month = <<<XML
SELECT
    c.Name,
    ROUND(SUM(c.ContainerStatus.PercentAvailability * c.ContainerStatus.Weight) / SUM(c.ContainerStatus.Weight), 2) as Availability
FROM Orion.Container c
WHERE
    MonthDiff(Tolocal(c.ContainerStatus.DateTime), GetDate()) = 1 AND
    c.Name = '{GROUP}'
XML;

?>