insert into public.users (
    name, date_of_birth, phone_number, email, address,
    id_type, id_number, employment_status, monthly_income,
    emergency_contact_name, emergency_contact_relationship, emergency_contact_phone,
    loan_type, password
)
select
    v.name, v.date_of_birth::date, v.phone_number, v.email, v.address,
    v.id_type, v.id_number, v.employment_status, v.monthly_income,
    v.emergency_contact_name, v.emergency_contact_relationship, v.emergency_contact_phone,
    v.loan_type,
    replace(extensions.crypt(encode(extensions.gen_random_bytes(12), 'hex'), extensions.gen_salt('bf', 10)), '$2a$', '$2y$')
from (values
    ('Maria Santos', '1992-03-14', '09171234567', 'maria.santos@example.com', '12 Mabini Street, Quezon City',
        'philsys', 'PSN-1001-2002', 'employed', 28000.00, 'Ramon Santos', 'Father', '09181234567', 'personal'),
    ('Juan Dela Cruz', '1988-07-02', '09281234567', 'juan.delacruz@example.com', '45 Rizal Avenue, Manila',
        'drivers_license', 'N01-23-456789', 'business_owner', 45000.00, 'Liza Dela Cruz', 'Spouse', '09391234567', 'business'),
    ('Ana Reyes', '1995-11-23', '09451234567', 'ana.reyes@example.com', '8 Bonifacio Road, Makati City',
        'umid', 'UMID-3300-4455', 'self_employed', 22000.00, 'Carla Reyes', 'Sister', '09561234567', 'emergency'),
    ('Carlo Mendoza', '1990-01-30', '09061234567', 'carlo.mendoza@example.com', '77 Luna Street, Pasig City',
        'sss', 'SSS-3412-9087', 'employed', 35000.00, 'Mina Mendoza', 'Mother', '09171230000', 'personal')
) as v (
    name, date_of_birth, phone_number, email, address,
    id_type, id_number, employment_status, monthly_income,
    emergency_contact_name, emergency_contact_relationship, emergency_contact_phone, loan_type
)
on conflict do nothing;

insert into public.loan_applications (
    user_id, loan_type, amount, approved_amount, term_months, purpose, status,
    interest_rate, total_interest, total_payable, monthly_amortization,
    outstanding_balance, repayment_status, admin_remarks, reviewed_by, reviewed_at
)
select
    u.id, v.loan_type, v.amount, v.approved_amount, v.term_months, v.purpose, v.status,
    8,
    case when v.status = 'approved' then round(v.approved_amount * 8 / 100 * v.term_months / 12, 2) else null end,
    case when v.status = 'approved' then round(v.approved_amount + round(v.approved_amount * 8 / 100 * v.term_months / 12, 2), 2) else null end,
    case when v.status = 'approved' then round((v.approved_amount + round(v.approved_amount * 8 / 100 * v.term_months / 12, 2)) / v.term_months, 2) else null end,
    case when v.status = 'approved' then round(v.approved_amount + round(v.approved_amount * 8 / 100 * v.term_months / 12, 2), 2) else null end,
    case when v.status = 'approved' then 'active' else null end,
    v.admin_remarks,
    case when v.status = 'pending' then null else (select id from public.admins order by id limit 1) end,
    case when v.status = 'pending' then null else now() - interval '2 days' end
from (values
    ('maria.santos@example.com', 'personal', 15000.00, 15000.00, 6, 'Tuition fees for my younger sibling', 'approved', 'Approved. Documents verified.'),
    ('juan.delacruz@example.com', 'business', 40000.00, 40000.00, 12, 'Additional stock for my sari-sari store', 'approved', null),
    ('ana.reyes@example.com', 'emergency', 8000.00, null, 3, 'Hospital bills for a family member', 'pending', null),
    ('carlo.mendoza@example.com', 'personal', 50000.00, null, 24, 'Home repairs after the recent typhoon', 'rejected', 'Requested amount exceeds current capacity.')
) as v (email, loan_type, amount, approved_amount, term_months, purpose, status, admin_remarks)
join public.users u on u.email = v.email;

update public.users u
set credits = coalesce((
        select sum(coalesce(la.approved_amount, la.amount))
        from public.loan_applications la
        where la.user_id = u.id and la.status = 'approved'
    ), 0),
    balance_owed = coalesce((
        select sum(coalesce(la.outstanding_balance, 0))
        from public.loan_applications la
        where la.user_id = u.id and la.repayment_status = 'active'
    ), 0);

insert into public.loan_payments (
    loan_application_id, user_id, reference_number, amount, payment_method, status, notes, confirmed_by, confirmed_at
)
select
    la.id, la.user_id, v.reference_number, v.amount, v.payment_method, v.status, v.notes,
    case when v.status = 'confirmed' then (select id from public.admins order by id limit 1) else null end,
    case when v.status = 'confirmed' then now() - interval '5 days' else null end
from (values
    ('maria.santos@example.com', 'GCASH-REF-1001', 2500.00, 'gcash', 'confirmed', 'First monthly payment'),
    ('juan.delacruz@example.com', 'BANK-REF-2002', 3500.00, 'bank_transfer', 'pending', 'Awaiting admin confirmation')
) as v (email, reference_number, amount, payment_method, status, notes)
join public.users u on u.email = v.email
join public.loan_applications la on la.user_id = u.id and la.status = 'approved' and la.repayment_status = 'active';

update public.loan_applications la
set outstanding_balance = greatest(0, outstanding_balance - coalesce((
    select sum(lp.amount)
    from public.loan_payments lp
    where lp.loan_application_id = la.id and lp.status = 'confirmed'
), 0))
where la.status = 'approved';

update public.users u
set balance_owed = coalesce((
        select sum(coalesce(la.outstanding_balance, 0))
        from public.loan_applications la
        where la.user_id = u.id and la.repayment_status = 'active'
    ), 0);
