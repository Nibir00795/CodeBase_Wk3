"""Lab 1, Task 4. Amortised loan payment, and the schedule behind it.

Author: Md Jonayed Hossain Chowdhury
Course: CPS 5745 Interactive Information Visualization

The formula is the standard amortised payment:

    M = P * i / (1 - (1 + i) ** -n)

    P = principal borrowed
    i = periodic interest rate = annual rate / 12 / 100
    n = number of payments = years * 12

Each month the interest due is the balance times i, whatever is left of the
payment reduces the balance, and the loan is repaid when the balance reaches
zero. When the rate is zero the formula divides by zero, so that case is the
straight line P / n and is handled separately.

Usage:  python3 loan.py <principal> <annual_rate_percent> <years>
Output: one JSON object on stdout, so the PHP page can decode it rather than
        scraping printed text.
"""

import json
import sys


def monthly_payment(principal, annual_rate_percent, years):
    """Return the level monthly payment for an amortised loan."""
    n = int(round(years * 12))
    i = annual_rate_percent / 100.0 / 12.0
    if n <= 0:
        raise ValueError("The term must be at least one month.")
    if i == 0:
        return principal / n, n, i
    payment = principal * i / (1 - (1 + i) ** -n)
    return payment, n, i


def build_schedule(principal, payment, n, i):
    """Walk the loan month by month and record what each payment does."""
    # Work in whole cents so the rows add up to the totals exactly, the way a
    # real statement does, instead of leaving a few cents of rounding drift.
    balance = round(principal, 2)
    payment = round(payment, 2)
    rows = []
    total_interest = 0.0
    for month in range(1, n + 1):
        interest = round(balance * i, 2)
        principal_part = round(payment - interest, 2)
        # The last payment absorbs the remainder so the balance lands on zero.
        if month == n or principal_part > balance:
            principal_part = balance
        balance = round(balance - principal_part, 2)
        total_interest += interest
        rows.append({
            "month": month,
            "interest": interest,
            "principal": principal_part,
            "balance": max(balance, 0.0),
        })
    return rows, round(total_interest, 2)


def main():
    if len(sys.argv) != 4:
        print(json.dumps({
            "ok": False,
            "error": "Three values are required: principal, annual rate, years.",
        }))
        sys.exit(1)

    try:
        principal = float(sys.argv[1])
        annual_rate_percent = float(sys.argv[2])
        years = float(sys.argv[3])
    except ValueError:
        print(json.dumps({"ok": False, "error": "Please enter valid numbers."}))
        sys.exit(1)

    if principal <= 0:
        print(json.dumps({"ok": False, "error": "The amount borrowed must be above zero."}))
        sys.exit(1)
    if annual_rate_percent < 0:
        print(json.dumps({"ok": False, "error": "The interest rate cannot be negative."}))
        sys.exit(1)
    if not 0 < years <= 50:
        print(json.dumps({"ok": False, "error": "The term must be between 1 and 50 years."}))
        sys.exit(1)

    payment, n, i = monthly_payment(principal, annual_rate_percent, years)
    schedule, total_interest = build_schedule(principal, payment, n, i)

    print(json.dumps({
        "ok": True,
        "inputs": {
            "principal": principal,
            "annual_rate_percent": annual_rate_percent,
            "years": years,
        },
        "monthly_payment": round(payment, 2),
        "payments": n,
        "total_interest": round(total_interest, 2),
        "total_repaid": round(principal + total_interest, 2),
        "interest_share_percent": round(100 * total_interest / (principal + total_interest), 1),
        "schedule": schedule,
    }))


if __name__ == "__main__":
    main()
