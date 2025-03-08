
{literal}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Condensed:wght@500&family=Roboto+Condensed&family=Roboto+Slab&display=swap" rel="stylesheet">

   <style>
    @media print,
    dompdf {

    html, body, p {
          border: 0;
          font-style: inherit;
          font-weight: inherit;
          margin: 0;
          outline: 0;
          padding: 0;
          vertical-align: baseline;
          font-family: 'Roboto Condensed', sans-serif;
        }
        html {
          -webkit-text-size-adjust: 100%;
          -ms-text-size-adjust:    100%;
          font-size: 1rem;
          font-family: 'Arial', sans-serif;
          font-weight: 400;
        }
        *,
        *:before,
        *:after {
          box-sizing: border-box;
        }
        main {
          display: block;
          width: 85.00mm;
          height: 55.0mm;
        }
        main img {
            display: block;
            width: 85.00mm;
            height: 55.0mm;
            position: absolute;
            top: 0;
            left: 0;
            z-index: -1;
        }
        p {
            z-index: 1;
            font-size: 9.5pt;
            line-height: 1.2;
            color: black;
            position: absolute;
            font-weight: bold;
        }
        #nome {
          top: 34.00mm;
          left: 3.50mm;
          font-size: 11pt;
          width: 52mm;
          white-space: nowrap;
          overflow: hidden;
        }
        #chapter-info {
          top: 42.80mm;
          left: 40.00mm;
        }
        #data {
          top: 48.70mm;
          left: 40.00mm;
          font-size: 0.85em;
        }
        #barcode {
          top: 19.0mm;
          left: 59.5mm;
        }
        #barcode img {
          height: 19.8mm;
          width: 19.8mm;
        }
    }
  </style>
{/literal}
<main>
  <img src="{$backgroundimgfront}">
  <p id="barcode"><img src="{$barcode}"></p>
  <p id="nome">{$first_name} {$last_name}</p>
  <p id="chapter-info">{$Sede_di_studio_Sede}</p>
  <p id="data">{$receive_date|date_format:"%d/%m/%Y"}</p>
</main>