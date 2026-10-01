# CPS 5745 — Lab 1 and the Welcome to Linux activity

Md Jonayed Hossain Chowdhury · Kean University · Fall 2026

## What is in this repository

| File | Task | What it does |
|---|---|---|
| `data_analysis.py` | 1 | Reads `sample.csv` with pandas and prints `info()` and `head()`. |
| `sample.csv` | 1 | Five courses, including CPS 5745, the extra class the handout asks for. |
| `Jonayed_Lab1.ipynb` | 2, 6 | Colab notebook: the student DataFrame with my own row added, then two Plotly charts. |
| `sum.py` | 3 | Adds two numbers passed as command-line arguments. |
| `sum.php` | 3 | Web form that runs `sum.py` and shows the result. |
| `loan.py` | 4 | My own formula: amortised loan payment, and the month-by-month schedule behind it. |
| `loan.php` | 4, extra credit | Web form that runs `loan.py` and charts the result with Plotly. |
| `MyPage.html` | Linux activity | Personal page with an image, served from `public_html`. |
| `students.png` | Linux activity | The image used by `MyPage.html`. |
| `plotly.min.js` | extra credit | Local copy of Plotly, used if the CDN is unreachable. |

## The formula in loan.py

A fixed-rate loan is repaid in equal monthly payments. The payment is

```
M = P × i / (1 − (1 + i)^−n)
```

`P` is the amount borrowed, `i` is the monthly interest rate (annual rate ÷ 12 ÷ 100),
and `n` is the number of payments (years × 12). When the rate is zero that expression
divides by zero, so the script handles it separately as `P / n`.

The script then walks the loan month by month: interest due is the outstanding balance
times `i`, whatever is left of the payment reduces the balance, and the loan closes when
the balance reaches zero. Everything is rounded to whole cents as it goes, so the monthly
rows add up to the totals exactly rather than drifting by a few cents.

Checked against a known case: $250,000 at 6.5% over 30 years gives a payment of $1,580.17
and total interest of $318,861.58. The principal column of the schedule sums to exactly
$250,000 and the final balance is $0.00.

## Deploying to obi2

From the folder holding these files on my Mac:

```
scp sum.py sum.php loan.py loan.php MyPage.html students.png plotly.min.js \
    'chowdmdj@kean.edu@obi2.kean.edu:~/public_html/'
```

Then over SSH:

```
ssh chowdmdj@kean.edu@obi2.kean.edu
cd public_html
chmod 755 sum.py sum.php loan.py loan.php MyPage.html students.png plotly.min.js
chmod 755 ~/public_html
ls -l
```

Pages:

- `https://obi2.kean.edu/~chowdmdj@kean.edu/MyPage.html`
- `https://obi2.kean.edu/~chowdmdj@kean.edu/sum.php`
- `https://obi2.kean.edu/~chowdmdj@kean.edu/loan.php`

## Welcome to Linux: the commands, in order

```
ssh chowdmdj@kean.edu@obi2.kean.edu     # 1. connect
ls                                      # what is in my home directory
mkdir NewDirectory                      # 2. create a directory
ls                                      # 3. check
cd NewDirectory                         # go into it
ls                                      # empty, nothing has been put in it yet
cd ..                                   # 4. back up one level
ls                                      # NewDirectory is now listed
mkdir public_html                       # 5. create public_html
ls -l                                   # now both directories show
chmod 755 public_html                   # 6. permissions on the folder
pwd                                     # 7. the full path to my home
chmod 755 /home/student/chowdmdj        # 8. use whatever pwd printed
cd public_html                          # 9. go in
ls -l
vi MyPage.html                          # 10. create the page
chmod 755 MyPage.html                   # 11. permissions on the file
history                                 # 13. the command history
```

In `vi`: press `i` to start typing, `Esc` then `:wq!` to save and quit, or `:q!` to quit
without saving.

Step 8 uses the path that `pwd` prints. The handout's example is
`/home/student/ykumar`, so mine is whatever `pwd` returns, not a guess.

## Answers to "what files are currently in your directory?"

These depend on what is already in my account, so each answer is what `ls` actually
returned at that point in the session, recorded in the submission PDF next to the
screenshot rather than written here in advance.

What is predictable is how the answer changes:

- Before step 2 the home directory holds whatever was already there.
- After `mkdir NewDirectory`, `ls` shows that name added.
- Inside `NewDirectory`, `ls` prints nothing, because a new directory is empty.
- After `cd ..` the listing is the home directory again, now including `NewDirectory`.
- After `mkdir public_html`, `ls -l` shows both new directories with `drwxr-xr-x`
  once `chmod 755` has been applied.
- Inside `public_html` before step 10, `ls -l` prints `total 0`.

## A note on the PHP in this repository

The handout builds the shell command with `escapeshellcmd` on the whole string. That
escapes shell metacharacters but does not keep the user's input from being read as a
separate argument. Both PHP files here use `escapeshellarg` on each argument instead,
which quotes each value as one token, so a value such as `3 7` or `3; ls` reaches
Python as text rather than changing what runs.
