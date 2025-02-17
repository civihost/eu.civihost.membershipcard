
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
        }
        main img {
            display: block;
            width: 210mm;
            height: 297mm;
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
        #data {
            top: 157.7mm;
            left: 67.625mm;
        }
        #numero {
            top: 157.7mm;
            left: 116.232mm;
        }
        #testo {
            top: 168mm;
            left: 67.625mm;
            width: 74.8mm
        }
        #cognome {
            left: 84.163mm;
            top: 182mm;
        }
        #nome {
            left: 84.163mm;
            top: 189.5mm;
        }
        .chapter-info {
          font-size: 0.85em;
          font-weight: normal;
          line-height: 0.90em;
        }
    }
  </style>
{/literal}
<main>
  <img src="{$backgroundimgfront}">
  <p id="data">{$receive_date|date_format:"%d/%m/%Y"}</p>
  <p id="numero">{$card_number}</p>
  <p id="testo">{$chapter_name}<br>
  <span class="chapter-info">{$chapter_email} {$chapter_url}</span></p>
  <p id="cognome">{$last_name}</p>
  <p id="nome">{$first_name}</p>
</main>