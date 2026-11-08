# How land rates are calculated

Mto Safi Municipality, Revenue Department. This is the rule the land rates
calculator on `land-rates.html` applies. Mto Safi is a fictional municipality,
so are these rates.

## The formula

    annual rate = rateable value × rate for the land use
    annual rate = at least the minimum rate
    interest    = annual rate × 1 % × months paid late
    total       = annual rate + interest

The total is rounded to whole Kenya shillings (KES).

## The values

| Value              | Amount                           |
|--------------------|----------------------------------|
| Residential land   | 0.5 % of the rateable value      |
| Commercial land    | 1.2 % of the rateable value      |
| Agricultural land  | 0.2 % of the rateable value      |
| Minimum rate       | KES 1,500 per plot and year      |
| Interest when late | 1 % of the annual rate per month |
| Due date           | 31 March of each year            |

## The terms

- **Rateable value**: the value of the plot in the valuation roll. The owner's
  valuation notice states it. An owner who disagrees files an objection to the
  valuation; until it is decided, the value in the roll applies.
- **Land use**: what the plot is used for according to the valuation roll. A
  plot with more than one use pays the rate of its main use.
- **Months paid late**: every month after 31 March that has started before the
  rate is paid in full. A rate paid on 10 June is three months late: April, May
  and June.
- **Interest** is simple: it is always 1 % of the annual rate, never of the
  interest of earlier months.

## Examples

| Land use     | Rateable value | Paid        | Annual rate | Interest | Total      |
|--------------|---------------:|-------------|------------:|---------:|-----------:|
| Residential  | KES 2,500,000  | 15 March    | KES 12,500  | KES 0    | KES 12,500 |
| Commercial   | KES 2,500,000  | 10 June     | KES 30,000  | KES 900  | KES 30,900 |
| Agricultural | KES 500,000    | 31 January  | KES 1,500 ¹ | KES 0    | KES 1,500  |

¹ 0.2 % of KES 500,000 is KES 1,000, which is below the minimum rate.

## What the calculator leaves out

- **Part payments.** Interest runs on the unpaid part of the rate only. The
  calculator assumes nothing was paid.
- **Arrears from earlier years.** Each year is calculated on its own.
- **Waiver of interest.** The municipality can waive the interest on
  application. The calculator shows the interest before any waiver.

## Where the calculator keeps these values

The rates are not part of the script. They are a data attribute on the
calculator in `land-rates.html`, so the website can let an editor change them:

```json
{
  "percentOfValue": { "residential": 0.5, "commercial": 1.2, "agricultural": 0.2 },
  "minimum": 1500,
  "interestPerMonth": 1
}
```

`js/calculator.js` reads them, and limits the months paid late to 0–36.
