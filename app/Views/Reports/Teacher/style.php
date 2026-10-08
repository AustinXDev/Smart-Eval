<style>

  @page {
    margin: 20mm 0;
  }

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-size: 12px;
    font-family: 'Arial', sans-serif;
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

  .name-container {
    display: table;
    table-layout: fixed;
    width: 100%;
    padding: 10px 34px;
    background-color: #534AB7;
  }

  .faculty-name,
  .name-subtext {
    display: table-cell;
    vertical-align: middle;
  }

  .faculty-name {
    width: 60%;
    font-size: 15px;
    letter-spacing: 0.08em;
    font-weight: bold;
    color: #fff;
  }

  .name-subtext {
    width: 40%;
    text-align: right;
    color: #AFA9EC;
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
    font-weight: bold;
    color: #3C3489;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-top: 5px;
    margin-bottom:15px;
    padding-bottom: 6px;
    border-bottom: 0.5px solid #AFA9EC;
    display: block;
  } 

  .section-title span{
    display: inline-block;
    width: 2px;
    height: 12px;
    background-color: #534AB7;
    vertical-align: middle;
    border-radius: 5px;
  }

  .overview{
    display: table;
    table-layout: fixed;
    width: 100%;
  }

  .overview-container .section-title {
    color: #16213E;
    border-bottom-color: #E4E1EC;
  }

  .overview-container .section-title span {
    background-color: #7C3AED;
  }

  .overview {
    display: table;
    width: 100%;
    table-layout: fixed;
  }

  .overall-score-container,
  .overview-cards {
    display: table-cell;
    vertical-align: top;
  }

  .overall-score-container {
    width: 35%;
    background-color: #F8F6F0;
    text-align: center;
    border-radius: 10px;
    padding: 35px 0;
  }

  .overall-score{
    font-size: 40px;
    font-weight: bold;
    color: #16213E;
    margin-bottom: 6px;
  }

  .overall-score-sub{
    font-size: 13px;
    color: #626779;
    margin-bottom: 8px;
  }

  .overview-cards {
    width: 65%;
    padding-left: 12px;
  }

  .adjective-rating{
    display: inline-block;
    padding: 5px 15px;
    background-color: #fff;
    border-radius: 12px;
    white-space: nowrap;
    font-weight: bold;
  }

  .overview-cards-2{
    display: table;
    table-layout: fixed;
    width: 100%;
    border-spacing: 10px 0;
  }

  .total-evaluated-container,
  .score-range-container {
    display: table-cell;
    vertical-align: top;
  }

  .total-evaluated-container{
    width: 60%;
    background-color: #F8F6F0;
    border-top: 3px solid #D8D2E8;
    border-radius: 5px;
    padding: 10px 15px;
  }

  .total-evaluated-title{
    font-size: 10px;
    font-weight: bold;
    color: #16213E;
    text-transform: uppercase;
  }

  .total-count {
    margin: 8px 0;
    font-size: 25px;
    font-weight: bolder;
    color: #5B21B6;
  }

  .total-sub-text{
    font-weight: light;
    color: #626779;
  }

  .score-range-container{
    width: 40%;
    background-color: #F8F6F0;
    border-top: 3px solid #D8D2E8;
    border-radius: 5px;
    padding: 10px 15px;
  }

  .score-range-title{
    font-size: 10px;
    font-weight: bold;
    color: #16213E;
    text-transform: uppercase;
  }

  .range {
    margin: 15px 0;
    font-size: 15px;
    font-weight: bolder;
    color: #16213E;
  }

  .score-range-subtext{
    font-weight: light;
    color: #626779;
  }

  .score-visual-container{
    margin: 8px 10px;
    background-color: #F8F6F0;
    padding: 10px 15px;
    border-radius: 5px;
  }

  .score-visual-title{
    font-size: 10px;
    font-weight: bold;
    color: #16213E;
    text-transform: uppercase;
  }

  .gauge{
    margin: 15px 0;
    height: 10px;
    background-color: #E5E4E8;
    border-radius: 10px;
  }

  .line{
    height: 100%;
    width: 50%;
    border-radius: 10px;
  }

  .mean-level{
    display: table;
    display: table;
    table-layout: fixed;
    width: 100%;
  }

  .low, .mid, .high{
    display: table-cell;
    vertical-align: top;
    width: calc(100% / 3);
    color: #626779;
  }

  .mid{
    text-align: center;
    color: #16213E;
  }

  .high{
    text-align: right;
  }

  .report-section {
    margin-top: 24px;
  }

  .report-section .section-title {
    margin-top: 0;
    margin-bottom: 10px;
  }

  .report-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
  }

  .report-table th {
    padding: 8px 10px;
    background-color: #F0EDF5;
    color: #16213E;
    font-size: 9px;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .report-table td {
    padding: 8px 10px;
    border-bottom: 1px solid #E4E1EC;
    color: #1A1A2E;
    font-size: 10px;
    line-height: 1.4;
    vertical-align: top;
    word-wrap: break-word;
  }

  .report-table tbody tr:nth-child(even) td {
    background-color: #FBFAF7;
  }

  .report-table .score-column {
    width: 25%;
    text-align: right;
    white-space: nowrap;
  }

  .report-table .question-number {
    width: 8%;
    text-align: center;
  }

  .empty-report-message {
    padding: 10px;
    border: 1px solid #E4E1EC;
    color: #626779;
    font-size: 10px;
  }

  .category-breakdown-container {
    margin-top: 24px;
  }

  .category-breakdown-container .section-title {
    color: #16213E;
    border-bottom-color: #E4E1EC;
  }

  .category-breakdown-container .section-title span {
    background-color: #7C3AED;
  }

  .category-breakdown-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
  }

  .category-breakdown-table th {
    padding: 8px 9px;
    background-color: #F0EDF5;
    color: #16213E;
    font-size: 9px;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .category-breakdown-table td {
    padding: 9px;
    border-bottom: 1px solid #E4E1EC;
    color: #1A1A2E;
    font-size: 10px;
    line-height: 1.35;
    vertical-align: middle;
  }

  .category-breakdown-table tbody tr:nth-child(even) td {
    background-color: #FBFAF7;
  }

  .category-breakdown-table .category-column {
    width: 30%;
  }

  .category-breakdown-table .category-score-column {
    width: 20%;
    text-align: center;
    white-space: nowrap;
  }

  .category-breakdown-table .category-visual-column {
    width: 32%;
    text-align: center;
  }

  .category-breakdown-table .category-delta-column {
    width: 18%;
    text-align: right;
    white-space: nowrap;
  }

  .category-name {
    font-weight: bold;
    color: #16213E;
  }

  .category-gauge {
    display: inline-block;
    width: 68%;
    height: 7px;
    margin-right: 5px;
    background-color: #E5E4E8;
    vertical-align: middle;
  }

  .category-gauge-fill {
    height: 7px;
    background-color: #7C3AED;
  }

  .category-gauge-percent {
    color: #626779;
    font-size: 9px;
    white-space: nowrap;
  }

  .delta-positive {
    color: #5B21B6 !important;
    font-weight: bold;
  }

  .delta-negative {
    color: #626779 !important;
    font-weight: bold;
  }

  .delta-neutral {
    color: #626779 !important;
  }

  .category-empty-message {
    padding: 10px;
    border: 1px solid #E4E1EC;
    color: #626779;
    font-size: 10px;
  }

  .question-gap-analysis-container {
    margin-top: 24px;
  }

  .question-gap-analysis-container > .section-title {
    color: #16213E;
    border-bottom-color: #E4E1EC;
  }

  .question-gap-analysis-container > .section-title span {
    background-color: #7C3AED;
  }

  .question-gap-columns {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
  }

  .question-gap-panel {
    width: 48%;
    padding: 0;
    vertical-align: top;
    border: 1px solid #E4E1EC;
  }

  .question-gap-spacer {
    width: 4%;
    border: none;
  }

  .question-gap-panel-title {
    padding: 8px 9px;
    color: #16213E;
    font-size: 10px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .strength-panel .question-gap-panel-title {
    background-color: #EDF4EC;
    border-bottom: 1px solid #D7E5D3;
  }

  .improvement-panel .question-gap-panel-title {
    background-color: #F8EEEE;
    border-bottom: 1px solid #EBD8D8;
  }

  .question-gap-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
  }

  .question-gap-table th {
    padding: 7px 6px;
    background-color: #F8F6F0;
    color: #626779;
    font-size: 8px;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: 0.03em;
  }

  .question-gap-table td {
    padding: 7px 6px;
    border-bottom: 1px solid #EAE8EE;
    color: #1A1A2E;
    font-size: 9px;
    line-height: 1.35;
    vertical-align: top;
  }

  .question-gap-table .gap-question-column {
    width: 52%;
  }

  .question-gap-table .gap-score-column {
    width: 28%;
    text-align: center;
  }

  .question-gap-table .gap-delta-column {
    width: 20%;
    text-align: right;
  }

  .gap-question {
    word-wrap: break-word;
  }

  .gap-score {
    text-align: center;
    white-space: nowrap;
    background-color: #fff;
    color: #16213E;
    font-weight: bold;
  }

  .gap-delta {
    text-align: right;
    white-space: nowrap;
    font-weight: bold;
  }

  .gap-positive {
    color: #355E35 !important;
  }

  .gap-negative {
    color: #8A4545 !important;
  }

  .gap-neutral {
    color: #626779 !important;
  }

  .question-gap-empty {
    padding: 9px;
    color: #626779;
    font-size: 9px;
  }

  .teacher-insights-container {
    margin-top: 24px;
    padding: 12px 14px;
    border: 1px solid #E4E1EC;
    background-color: #FBFAF7;

    page-break-inside: auto;
    break-inside: auto;
  }

  .teacher-insights-container > .section-title {
    margin-top: 0;
    margin-bottom: 12px;
    color: #16213E;
    border-bottom-color: #E4E1EC;
  }

  .teacher-insights-container > .section-title span {
    background-color: #7C3AED;
  }

  .teacher-insight-overall {
    padding: 10px 11px;
    border-left: 3px solid #7C3AED;
    background-color: #fff;
  }

  .teacher-insight-label {
    margin-bottom: 4px;
    color: #5B21B6;
    font-size: 9px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .teacher-insight-overall p,
  .teacher-insight-group p,
  .teacher-insight-neutral {
    margin: 0;
    color: #1A1A2E;
    font-size: 9px;
    line-height: 1.5;
  }

  .teacher-insight-group {
    padding-top: 9px;
    margin-top: 9px;
    border-top: 1px solid #E4E1EC;
  }

  .teacher-insight-group p + p {
    margin-top: 5px;
  }

  .teacher-insight-neutral {
    padding-top: 9px;
    margin-top: 9px;
    border-top: 1px solid #E4E1EC;
    color: #626779;
  }

</style>