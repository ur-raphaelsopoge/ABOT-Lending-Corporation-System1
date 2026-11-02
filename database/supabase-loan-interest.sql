alter table public.loan_applications
    add column if not exists interest_rate numeric(5, 2) not null default 8
        check (interest_rate >= 0 and interest_rate <= 100),
    add column if not exists approved_amount numeric(12, 2)
        check (approved_amount is null or (approved_amount >= 1000 and approved_amount <= 50000)),
    add column if not exists total_interest numeric(12, 2),
    add column if not exists total_payable numeric(12, 2),
    add column if not exists monthly_amortization numeric(12, 2);

update public.loan_applications
set approved_amount = amount
where status = 'approved' and approved_amount is null;

update public.loan_applications
set interest_rate = 8,
    total_interest = round(coalesce(approved_amount, amount) * 8 / 100 * term_months / 12, 2)
where status = 'approved';

update public.loan_applications
set total_payable = round(coalesce(approved_amount, amount) + total_interest, 2),
    monthly_amortization = round((coalesce(approved_amount, amount) + total_interest) / term_months, 2)
where status = 'approved';
