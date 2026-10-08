<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    font-size: 12px;
    color: #1A1714;
    background: #fff;
}

.cover-band {
background: #2D1B69;
padding: 24px 34px 21px;
}

.school-name {
font-size: 13px;
font-weight: 700;
color: #fff;
letter-spacing: 0.2px;
line-height: 1.3;
margin-bottom: 4px;
}

.school-addr {
font-size: 9px;
color: #D1CDFA;
line-height: 1.4;
}

.report-title {
font-size: 18px;
font-weight: 700;
color: #fff;
line-height: 1.25;
margin-top: 17px;
margin-bottom: 7px;
}

.report-sub {
font-size: 9px;
color: #D1CDFA;
line-height: 1.5;
padding-top: 8px;
border-top: 1px solid #534AB7;
}

.cover-logo {
float: right;
display: block;
width: 48px;
height: auto;
margin: 0 0 10px 18px;
}

.cover-clearfix {
clear: both;
}

.cover-rule {
height: 3px;
background: #534AB7;
}

.body-wrapper {
    padding: 24px 36px 10px;
}

.section-title {
    font-size: 10px;
    font-weight: 600;
    color: #3C3489;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-top: 22px;
    margin-bottom: 10px;
    padding-bottom: 6px;
    border-bottom: 0.5px solid #AFA9EC;
    display: block;
}

/* --- Stats Grid --- */
table.stat-grid {
    width: 100%;
    border-collapse: separate;
    border-spacing: 12px;
    margin-bottom: 24px;
}

table.stat-grid td.stat-card {
    width: 32%;
    background: #FAFAF8;
    border: 0.5px solid #AFA9EC;
    border-radius: 8px;
    padding: 16px 12px;
    text-align: center;
    vertical-align: top;
}

table.stat-grid td.stat-card.neutral {
    background: #F5F4FA;
}

table.stat-grid td.stat-card.hero-card {
    background: #2D1B69; 
    border: 0.5px solid #1D1145;
    border-radius: 8px;
    padding: 16px 12px;
    text-align: center;
    vertical-align: top;
}

.hero-card .stat-title {
    color: #AFA9EC;
    font-size: 12px;
    font-weight: bold;
    text-transform: capitalize;
    margin-bottom: 2px;
}

.hero-card .score-value {
    color: #FFFFFF; 
    font-size: 20px;
    font-weight: bold;
    margin: 6px 0;
    letter-spacing: -0.02em;
}

.hero-card .stat-sub {
    color: #D1CDFA;
    font-size: 10px;
    font-weight: 500;
}

/* --- Outer 2-Column Layout Container --- */
table.perf-wrapper-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    margin-bottom: 24px;
}

table.perf-wrapper-table > tbody > tr > td {
    border: none;
    padding: 0;
    vertical-align: top;
}

td.perf-col-cell {
    width: 48.5%;
}

td.perf-col-gap {
    width: 3%; 
}

/* --- Section Labels --- */
.col-label {
    font-size: 9px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 6px 10px;
    border-radius: 6px 6px 0 0;
    border: 0.5px solid #AFA9EC;
    border-bottom: none;
}

.col-label.high {
    background: #EAF3E6;
    color: #27500A;
}

.col-label.low {
    background: #FCE8E8;
    color: #791F1F;
}

/* --- Performance Tables --- */
table.perf-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    border: 0.5px solid #AFA9EC;
    border-radius: 0 0 6px 6px;
}

table.perf-table thead {
    display: table-header-group;
}

/* OPTIMIZATION: Allowed rows to break naturally to prevent Dompdf engine freezes */
table.perf-table tr {
    page-break-inside: auto; 
}

table.perf-table th {
    background: #2D1B69;
    color: #AFA9EC;
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    padding: 6px 8px;
    border: none;
}

table.perf-table td {
    padding: 7px 8px;
    font-size: 10px;
    line-height: 1.3;
    color: #1A1714;
    border-bottom: 0.5px solid #EEECE7;
    vertical-align: middle;
}

table.perf-table tr:last-child td {
    border-bottom: none;
}

table.perf-table tr:nth-child(even) td {
    background: #FAFAF8;
}

/* Column Width Adjustments */
.col-question {
    width: 78%;
    text-align: left;
}

.col-score {
    width: 22%;
}

.text-left {
    text-align: left;
}

.center {
    text-align: center;
}

.score-high {
    font-weight: bold;
    color: #27500A;
}

.score-low {
    font-weight: bold;
    color: #791F1F;
}

.no-data {
    color: #6B6B60;
    font-style: italic;
    padding: 12px 8px;
}

.keep-together {
    page-break-inside: avoid !important;
    break-inside: avoid !important;
}

.section-title {
    page-break-after: avoid !important;
    break-after: avoid !important;
}

/* --- Global Tables --- */
table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed; /* OPTIMIZATION: Speeds up table dimension calculation */
}

thead {
    display: table-header-group;
}

/* OPTIMIZATION: Changed from 'avoid' to 'auto' globally */
tr {
    page-break-inside: auto; 
}

th {
    background: #2D1B69;
    padding: 8px 10px;
    text-align: left;
    font-size: 9px;
    font-weight: 600;
    color: #AFA9EC;
    text-transform: uppercase;
}

th.center,
td.center {
    text-align: center;
}

td {
    padding: 8px 10px;
    font-size: 11px;
    color: #1A1714;
    border-bottom: 0.5px solid #EEECE7;
}

tr:nth-child(even) td {
    background: #FAFAF8;
}

.table-wrap {
    border: 0.5px solid #AFA9EC;
    border-radius: 8px;
}

/* Executive Insight Callout Box */
.insight-box {
    width: 95%;
    box-sizing: border-box;
    border: 1px solid #d9d6e3;
    background: #faf9fc;
    padding: 18px 20px;
    page-break-inside: avoid;
    margin-top: 15px;
    margin-bottom: 20px;
}

.insight-title {
    font-size: 16px;
    font-weight: bold;
    color: #24134f;
    margin-bottom: 4px;
}

.insight-subtitle {
    font-size: 9px;
    color: #777777;
    margin-bottom: 15px;
}

.insight-metrics {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    margin-bottom: 16px;
}

.insight-metrics td.metric-card {
    width: 32%;
    background: #ffffff;
    border: 1px solid #e2dfea;
    border-radius: 6px;
    padding: 10px 12px;
    vertical-align: top;
}

.insight-metrics td.metric-gap {
    width: 2%;
    background: transparent;
    border: none;
}

.metric-label {
    font-size: 8px;
    text-transform: uppercase;
    color: #777777;
    margin-bottom: 4px;
}

.metric-value {
    font-size: 18px;
    font-weight: bold;
    color: #24134f;
}

.metric-description {
    font-size: 8px;
    color: #777777;
    margin-top: 2px;
}

.insight-section {
    border-top: 1px solid #e3e0e8;
    padding-top: 10px;
    margin-top: 10px;
}

.section-label {
    font-size: 9px;
    font-weight: bold;
    text-transform: uppercase;
    color: #5b21b6;
    margin-bottom: 4px;
}

.insight-body {
    font-size: 10px;
    line-height: 1.55;
    color: #333333;
    margin: 0;
}

.footer {
    margin-top: 2.5rem;
    padding: 10px 36px;
    border-top: 2px solid #2D1B69;
    display: table;
    width: 100%;
    background: #EEEDFE;
}

.footer-left {
    display: table-cell;
    font-size: 9px;
    color: #3C3489;
}

.footer-right {
    display: table-cell;
    text-align: right;
    font-size: 9px;
    color: #534AB7;
}



@page {
    size: A4;
    margin: 0;
}


</style>