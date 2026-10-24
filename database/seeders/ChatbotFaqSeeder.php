<?php

namespace Database\Seeders;

use App\Models\ChatbotFaq;
use Illuminate\Database\Seeder;

class ChatbotFaqSeeder extends Seeder
{
    /**
     * Single source of truth for Diabot's preset answers.
     *
     * ChatbotService::defaultFaqs() is intentionally empty — add new FAQs
     * here, then run: php artisan db:seed --class=ChatbotFaqSeeder
     *
     * {{clinic_name}}, {{clinic_address}}, {{clinic_contact_number}},
     * {{clinic_email}}, {{operating_hours}} are replaced at answer time
     * with live values from the Settings page (see ChatbotService::
     * applySettingPlaceholders()) — never hardcode those facts here.
     */
    public function run(): void
    {
        $faqs = [

            // ===== A. General dialysis information =====
            ['question' => 'What is dialysis?', 'answer' => 'Dialysis is a treatment that does some of the work healthy kidneys normally do — removing waste, extra salt, and extra fluid from your blood when your kidneys can no longer do this well enough on their own.', 'keywords' => 'what is dialysis, dialysis treatment, kidney failure treatment, renal replacement'],
            ['question' => 'What is hemodialysis?', 'answer' => 'Hemodialysis is a treatment that filters waste, extra fluid, and salt from your blood when your kidneys can no longer do this well enough on their own.', 'keywords' => 'hemodialysis, haemodialysis, filter blood, blood filtering, dialysis process'],
            ['question' => 'Why would someone need dialysis?', 'answer' => 'Dialysis is usually needed when the kidneys have lost most of their ability to filter the blood, often due to chronic kidney disease or kidney failure. Your doctor is the one who determines if and when dialysis is needed for you.', 'keywords' => 'why dialysis, need dialysis, kidney failure, reason for dialysis, when is dialysis needed'],
            ['question' => 'What happens during a dialysis session?', 'answer' => 'You are connected to the dialysis machine through your access site, your blood is filtered gradually over the session, and staff monitor your blood pressure and how you feel throughout.', 'keywords' => 'what happens during dialysis, during a session, during treatment, what to expect dialysis'],
            ['question' => 'How long does a dialysis session take?', 'answer' => 'A typical hemodialysis session lasts about 3 to 4 hours, usually done a few times a week, depending on your doctor\'s recommendation.', 'keywords' => 'how long, duration, session length, how many hours, how long is treatment'],
            ['question' => 'How often do patients need dialysis?', 'answer' => 'Most hemodialysis patients are treated about three times a week, but your exact schedule is set by your doctor based on your condition.', 'keywords' => 'how often, how many times a week, dialysis frequency, how many sessions a week'],
            ['question' => 'Is dialysis painful?', 'answer' => 'Dialysis itself is not usually painful, though you may feel a brief pinch when the needles are inserted at the start of a session. Tell staff right away if you feel real pain during treatment.', 'keywords' => 'is dialysis painful, does dialysis hurt, pain during dialysis, does it hurt'],
            ['question' => 'What might I feel during dialysis?', 'answer' => 'Many patients feel fine, though some notice mild tiredness, light-headedness, or muscle cramps. Tell staff immediately if you feel unwell during your session.', 'keywords' => 'feel during dialysis, side effects during session, how will i feel during treatment'],
            ['question' => 'Does dialysis cure kidney disease?', 'answer' => 'No. Dialysis replaces some kidney functions but does not cure kidney disease. Your doctor will discuss your overall treatment plan and options with you.', 'keywords' => 'cure kidney disease, does dialysis cure, is dialysis permanent, is dialysis a cure'],

            // ===== B. Preparing for dialysis =====
            ['question' => 'What should I bring to my dialysis appointment?', 'answer' => 'Please bring a valid ID, your patient reference code, and any current medications or prescriptions. Arrive at least 15 minutes early.', 'keywords' => 'bring to dialysis, things to bring, appointment requirements, documents for dialysis, bring appointment, bring'],
            ['question' => 'What should I wear for dialysis?', 'answer' => 'Wear loose, comfortable clothing that lets staff easily reach your dialysis access site, such as a short-sleeved top if your access is in your arm.', 'keywords' => 'what to wear, clothing for dialysis, dress for dialysis session'],
            ['question' => 'Should I eat before my dialysis session?', 'answer' => 'Follow the specific eating guidance your doctor or dialysis care team gave you — this can vary by patient. If you\'re unsure, ask your care team before your next session.', 'keywords' => 'eat before dialysis, food before treatment, can i eat before session'],
            ['question' => 'Should I take my regular medications before dialysis?', 'answer' => 'Follow your doctor\'s specific instructions about your medications on dialysis days — do not skip or add doses on your own. Ask your care team if you are unsure.', 'keywords' => 'medication before dialysis, take my medicine, skip medication dialysis day'],
            ['question' => 'How do I follow my fluid restrictions?', 'answer' => 'Your care team sets a personal fluid limit based on your condition. Ask your doctor or nurse for your specific target — we can\'t give you a general number here since it varies by patient.', 'keywords' => 'fluid restriction, how much water, fluid limit, drink water dialysis'],
            ['question' => 'How should I prepare for my first dialysis session?', 'answer' => 'Arrive a little early, bring a valid ID and any referral or lab documents your doctor gave you, and don\'t hesitate to ask staff questions — they will walk you through what to expect.', 'keywords' => 'first dialysis session, first time dialysis, new patient prepare, prepare for dialysis'],
            ['question' => 'What should I do if I feel unwell before my appointment?', 'answer' => 'Please contact our dialysis staff or your doctor right away if you feel unwell. Do not wait for your next scheduled visit.', 'keywords' => 'feel unwell, side effects, not feeling well, sick before appointment'],
            ['question' => 'What should I discuss with my doctor before starting dialysis?', 'answer' => 'It helps to discuss your treatment schedule, dietary and fluid guidance, your access type, and any concerns you have. Write your questions down beforehand so you don\'t forget them.', 'keywords' => 'discuss with doctor, questions for doctor, before starting dialysis'],

            // ===== C. Appointments and scheduling =====
            ['question' => 'How do I request an appointment?', 'answer' => 'Appointments are set up by our staff. Please contact {{clinic_contact_number}} or visit the center directly to request one.', 'keywords' => 'request appointment, book appointment, set appointment, new appointment, schedule a visit'],
            ['question' => 'How do I check my appointment schedule?', 'answer' => 'You can check your appointment using the "Check My Appointment" page on our homepage. You will need your patient reference code and birthdate.', 'keywords' => 'check appointment, schedule, view appointment, when is my appointment, reference code, my dialysis schedule'],
            ['question' => 'How do I confirm my appointment?', 'answer' => 'Please contact {{clinic_contact_number}} to confirm your upcoming appointment with our staff.', 'keywords' => 'confirm appointment, verify my appointment, confirm my schedule'],
            ['question' => 'How do I reschedule or cancel my appointment?', 'answer' => 'Please contact our staff directly by phone or visit the center at least 24 hours before your scheduled appointment.', 'keywords' => 'reschedule, cancel, change appointment, move appointment, cancel my session'],
            ['question' => 'What should I do if I will be late for my appointment?', 'answer' => 'Please contact {{clinic_contact_number}} as soon as possible if you\'re running late, so staff can advise you on your options.', 'keywords' => 'late for appointment, running late, delayed for dialysis'],
            ['question' => 'What happens if I miss my dialysis session?', 'answer' => 'Please contact us right away to reschedule — missing a session can affect your treatment plan, so don\'t wait until your next scheduled visit to let us know.', 'keywords' => 'miss appointment, missed session, no show, skip dialysis session'],
            ['question' => 'Do you accept walk-in patients?', 'answer' => 'We recommend setting an appointment first. Please contact our staff to check same-day availability for walk-ins.', 'keywords' => 'walk-in, walk in, without appointment, same day dialysis'],
            ['question' => 'Are you open on weekends or holidays?', 'answer' => 'Our regular hours are {{operating_hours}}. Please contact {{clinic_contact_number}} to confirm holiday schedules.', 'keywords' => 'weekends, holidays, open sunday, open saturday, holiday schedule'],
            ['question' => 'How can I contact the center about a schedule change?', 'answer' => 'You can reach us at {{clinic_contact_number}} or {{clinic_email}} for any schedule changes.', 'keywords' => 'contact about schedule, call to reschedule, number to call for appointment'],

            // ===== D. Symptoms, complications, and patient safety =====
            ['question' => 'What might I feel after dialysis?', 'answer' => 'Some tiredness after a session is common and usually passes within a few hours. Tell your care team if fatigue is severe or doesn\'t improve.', 'keywords' => 'feel after dialysis, tired after treatment, fatigue after dialysis'],
            ['question' => 'Is it normal to have muscle cramps or dizziness during dialysis?', 'answer' => 'Cramps and light dizziness can happen during a session. Tell staff immediately if this happens so they can help you — don\'t wait until after your session.', 'keywords' => 'muscle cramps, cramping during dialysis, dizziness during dialysis, feeling dizzy dialysis'],
            ['question' => 'What symptoms need urgent medical attention?', 'answer' => 'Severe chest pain, trouble breathing, fainting, or very heavy bleeding are emergencies — call emergency services or go to the nearest emergency room immediately. Do not wait for your next appointment.', 'keywords' => 'urgent symptoms, emergency symptoms, when to seek help, serious symptoms, chest pain dialysis'],
            ['question' => 'What should I do if my dialysis access is bleeding?', 'answer' => 'Apply firm, steady pressure to the site and contact the clinic or emergency services right away. Do not wait to see if it stops on its own.', 'keywords' => 'access bleeding, fistula bleeding, graft bleeding, bleeding access site'],
            ['question' => 'What should I do if I have a fever or signs of infection at my access site?', 'answer' => 'Contact our clinic staff or your doctor promptly — redness, swelling, warmth, or fever near your access site should not wait until your next scheduled visit.', 'keywords' => 'fever, infection, access site infection, signs of infection, red swollen access'],
            ['question' => 'I am worried about my treatment, who should I talk to?', 'answer' => 'Please talk to our dialysis staff or your doctor — they are there to answer your questions and address any concerns about your treatment.', 'keywords' => 'worried about treatment, concerned about dialysis, talk to someone, anxious about treatment'],

            // ===== E. Billing and administrative questions =====
            ['question' => 'How much does dialysis treatment cost?', 'answer' => 'Costs can vary, and Diabot isn\'t able to quote fees. Please contact our billing staff at {{clinic_contact_number}} for accurate pricing information.', 'keywords' => 'cost of dialysis, price, how much is dialysis, treatment fees, dialysis cost'],
            ['question' => 'What payment methods do you accept?', 'answer' => 'Please ask our billing staff directly about accepted payment methods — this isn\'t something Diabot can confirm.', 'keywords' => 'payment methods, how to pay, accepted payments, ways to pay'],
            ['question' => 'Can I get an official receipt?', 'answer' => 'Yes, please ask our billing staff for an official receipt after your payment.', 'keywords' => 'official receipt, billing document, ask for receipt'],
            ['question' => 'Do you accept PhilHealth or insurance?', 'answer' => 'Please check with our billing staff directly about PhilHealth or insurance coverage, as this depends on your specific plan and documents.', 'keywords' => 'philhealth, insurance, insurance coverage, covered by insurance, philhealth coverage'],
            ['question' => 'What documents do I need for billing or registration?', 'answer' => 'Please ask our staff for the exact list of documents needed for your billing or registration — requirements can vary by patient.', 'keywords' => 'documents needed, registration requirements, what documents for billing'],
            ['question' => 'Who do I talk to about my bill or a payment arrangement?', 'answer' => 'Please speak with our billing staff directly about your bill or to discuss a payment arrangement.', 'keywords' => 'payment arrangement, talk about my bill, billing inquiry, installment payment'],

            // ===== F. General clinic information =====
            ['question' => 'Where is the dialysis center located?', 'answer' => 'We are located at {{clinic_address}}.', 'keywords' => 'location, address, where are you located, directions to clinic'],
            ['question' => 'What are your operating hours?', 'answer' => 'Our center is open {{operating_hours}}. Please contact {{clinic_contact_number}} to confirm holiday schedules.', 'keywords' => 'hours, operating hours, what time, when are you open, working hours'],
            ['question' => 'How can I contact the center?', 'answer' => 'You may reach us at {{clinic_contact_number}} or {{clinic_email}}, or visit us directly at {{clinic_address}}.', 'keywords' => 'contact the center, phone number, email, how to reach you'],
            ['question' => 'What services does the center offer?', 'answer' => 'We provide hemodialysis treatment and related patient care. For a full list of services, please ask our staff.', 'keywords' => 'services offered, what services, what do you offer, treatments offered'],
            ['question' => 'Do I need a referral to start treatment here?', 'answer' => 'Please confirm referral and eligibility requirements with our clinic staff or your nephrologist before starting treatment.', 'keywords' => 'need a referral, eligibility, requirements to start treatment, referral letter'],

            // ===== G. Application and account assistance (staff users) =====
            ['question' => 'How do I find a patient\'s record?', 'answer' => 'Use the Patients section in the sidebar and search by name or patient code to find a record.', 'keywords' => 'find patient record, search patient, locate patient, patient lookup staff'],
            ['question' => 'Where can I see a patient\'s appointments?', 'answer' => 'Open the patient\'s record, or go to the Appointments section and filter by patient.', 'keywords' => 'patient appointments, view appointments, appointment list for patient'],
            ['question' => 'How do I view treatment records?', 'answer' => 'Go to the Treatment Records section in the sidebar to view or add session records.', 'keywords' => 'treatment records, view treatment, session records, dialysis records'],
            ['question' => 'Where can I see billing or charges?', 'answer' => 'Go to the Charges section in the sidebar to view a patient\'s charges and payment status.', 'keywords' => 'view charges, billing section, see charges, payment status staff'],
            ['question' => 'Where can I find reports?', 'answer' => 'Go to the Reports section in the sidebar for patient and appointment reports.', 'keywords' => 'find reports, reports section, generate report, view reports'],
            ['question' => 'What should I do if a page shows an error?', 'answer' => 'Try refreshing the page first. If the error continues, please contact your system administrator with details of what you were doing.', 'keywords' => 'page error, error message, something went wrong, page not working'],
            ['question' => 'Who do I contact about missing permissions or access problems?', 'answer' => 'Please contact your system administrator about missing permissions or access problems — they manage staff accounts and roles.', 'keywords' => 'missing permission, access denied, cant access, permission problem, cant see a page'],
        ];

        foreach ($faqs as $faq) {
            ChatbotFaq::updateOrCreate(['question' => $faq['question']], $faq);
        }
    }
}