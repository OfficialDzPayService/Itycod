<?php
/**
 * Add or modify States
 */
add_filter('woocommerce_states', 'custom_woocommerce_states');

function custom_woocommerce_states($states) {
    $states['DZ'] = array(
        // State codes and names...
        'DZ-01' => '01 Adrar - أدرار',
        'DZ-02' => '02 Chlef - الشلف',
        'DZ-03' => '03 Laghouat - الأغواط',
        'DZ-04' => '04 Oum El Bouaghi - أم البواقي',
        'DZ-05' => '05 Batna - باتنة', 
        'DZ-06' => '06 Béjaïa - بجاية',
        'DZ-07' => '07 Biskra - بسكرة',
        'DZ-08' => '08 Bechar - بشار',
        'DZ-09' => '09 Blida - البليدة',
        'DZ-10' => '10 Bouira - البويرة',
        'DZ-11' => '11 Tamanrasset - تمنراست ',
        'DZ-12' => '12 Tébessa - تبسة ',
        'DZ-13' => '13 Tlemcene - تلمسان',
        'DZ-14' => '14 Tiaret - تيارت',
        'DZ-15' => '15 Tizi Ouzou - تيزي وزو',
        'DZ-16' => '16 Alger - الجزائر',
        'DZ-17' => '17 Djelfa - الجلفة',
        'DZ-18' => '18 Jijel - جيجل',
        'DZ-19' => '19 Sétif - سطيف',
        'DZ-20' => '20 Saïda - سعيدة',
        'DZ-21' => '21 Skikda - سكيكدة',
        'DZ-22' => '22 Sidi Bel Abbès - سيدي بلعباس',
        'DZ-23' => '23 Annaba - عنابة',
        'DZ-24' => '24 Guelma - قالمة',
        'DZ-25' => '25 Constantine - قسنطينة',
        'DZ-26' => '26 Médéa - المدية',
        'DZ-27' => '27 Mostaganem - مستغانم',
        'DZ-28' => '28 MSila - مسيلة',
        'DZ-29' => '29 Mascara - معسكر',
        'DZ-30' => '30 Ouargla - ورقلة',
        'DZ-31' => '31 Oran - وهران',
        'DZ-32' => '32 El Bayadh - البيض',
        'DZ-33' => '33 Illizi - إليزي ',
        'DZ-34' => '34 Bordj Bou Arreridj - برج بوعريريج',
        'DZ-35' => '35 Boumerdès - بومرداس',
        'DZ-36' => '36 El Tarf - الطارف',
        'DZ-37' => '37 Tindouf - تندوف',
        'DZ-38' => '38 Tissemsilt - تيسمسيلت',
        'DZ-39' => '39 Eloued - الوادي',
        'DZ-40' => '40 Khenchela - خنشلة',
        'DZ-41' => '41 Souk Ahras - سوق أهراس',
        'DZ-42' => '42 Tipaza - تيبازة',
        'DZ-43' => '43 Mila - ميلة',
        'DZ-44' => '44 Aïn Defla - عين الدفلى',
        'DZ-45' => '45 Naâma - النعامة',
        'DZ-46' => '46 Aïn Témouchent - عين تموشنت',
        'DZ-47' => '47 Ghardaïa - غرداية',
        'DZ-48' => '48 Relizane- غليزان',
        'DZ-49' => '49 Timimoun - تيميمون',
        'DZ-50' => '50 Bordj Baji Mokhtar - برج باجي مختار',
        'DZ-51' => '51 Ouled Djellal - أولاد جلال',
        'DZ-52' => '52 Béni Abbès -  بني عباس',
        'DZ-53' => '53 Aïn Salah - عين صالح',
        'DZ-54' => '54 In Guezzam - عين قزام',
        'DZ-55' => '55 Touggourt - تقرت',
        'DZ-56' => '56 Djanet - جانت',
        'DZ-57' => '57 El MGhair - المغير',
        'DZ-58' => '58 El Menia - المنيعة',
    );
        
		// United arab emirates states
    $states['AE'] = array(
    'DXB' => __('Dubai', 'textdomain'),
    'AUH' => __('Abu Dhabi', 'textdomain'),
    'SHJ' => __('Sharjah', 'textdomain'),
    'AJM' => __('Ajman', 'textdomain'),
    'FUJ' => __('Fujairah', 'textdomain'),
    'RAK' => __('Ras Al Khaimah', 'textdomain'),
    'UAQ' => __('Umm Al Quwain', 'textdomain'),
);

    // Qatar states
    $states['QA'] = array(
        'QA-DA' => 'Doha - الدوحة',
        'QA-KH' => 'Al Khor - الخور',
        'QA-WA' => 'Al Wakrah - الوكرة',
        'QA-RA' => 'Al Rayyan - الريان',
        'QA-UM' => 'Umm Salal - أم صلال',
        'QA-MS' => 'Madinat ash Shamal - الشمال',
        'QA-JU' => 'Al Jumaliyah - الجميلية',
        'QA-DU' => 'Dukhan - دخان',
        'QA-SH' => 'Ash Shihaniyah - الشحانية',
    );
    
    // Oman states
    $states['OM'] = array(
        'OM-BA' => 'Al Batinah North - شمال الباطنة',
        'OM-BB' => 'Al Batinah South - جنوب الباطنة',
        'OM-DA' => 'Ad Dakhiliyah - الداخلية',
        'OM-SH' => 'Ash Sharqiyah North - شمال الشرقية',
        'OM-SS' => 'Ash Sharqiyah South - جنوب الشرقية',
        'OM-WU' => 'Al Wusta - الوسطى',
        'OM-ZA' => 'Az Zahirah - الظاهرة',
        'OM-MA' => 'Muscat - مسقط',
        'OM-BU' => 'Musandam - مسندم',
        'OM-DH' => 'Dhofar - ظفار',
    );

    // Saudi Arabia states
    $states['SA'] = array(
        'SA-RI' => 'Riyadh - الرياض',
        'SA-MK' => 'Makkah - مكة',
        'SA-MD' => 'Madinah - المدينة',
        'SA-QA' => 'Al-Qassim - القصيم',
        'SA-EP' => 'Eastern Province - الشرقية',
        'SA-AS' => 'Asir - عسير',
        'SA-BA' => 'Al Baha - الباحة',
        'SA-JF' => 'Jouf - الجوف',
        'SA-NJ' => 'Najran - نجران',
        'SA-HS' => 'Hail - حائل',
        'SA-TB' => 'Tabuk - تبوك',
        'SA-JZ' => 'Jazan - جازان',
        'SA-BAH' => 'Northern Borders - الحدود الشمالية',
    );

    // Moroccan States
    $states['MA'] = array(
        'MA-01' => '01 Tanger-Tétouan-Al Hoceïma - طنجة-تطوان-الحسيمة',
        'MA-02' => '02 Oriental - الشرق',
        'MA-03' => '03 Fès-Meknès - فاس-مكناس',
        'MA-04' => '04 Rabat-Salé-Kénitra - الرباط-سلا-القنيطرة',
        'MA-05' => '05 Béni Mellal-Khénifra - بني ملال-خنيفرة',
        'MA-06' => '06 Casablanca-Settat - الدار البيضاء-سطات',
        'MA-07' => '07 Marrakech-Safi - مراكش-آسفي',
        'MA-08' => '08 Drâa-Tafilalet - درعة-تافيلالت',
        'MA-09' => '09 Souss-Massa - سوس-ماسة',
        'MA-10' => '10 Guelmim-Oued Noun - كلميم-واد نون',
        'MA-11' => '11 Laâyoune-Sakia El Hamra - العيون-الساقية الحمراء',
        'MA-12' => '12 Dakhla-Oued Ed-Dahab - الداخلة-وادي الذهب',
    );

    return $states;
}
?>