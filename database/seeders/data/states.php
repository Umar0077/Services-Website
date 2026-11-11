<?php

/*
| All 50 states are seeded as records. Only states with written content below are published for review,
| and every state starts with coverage_verified = false, so nothing is indexed until the business confirms
| provider coverage in Admin → Locations.
*/

return [
    'list' => [
        'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California',
        'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'FL' => 'Florida', 'GA' => 'Georgia',
        'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa',
        'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
        'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi', 'MO' => 'Missouri',
        'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey',
        'NM' => 'New Mexico', 'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio',
        'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
        'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont',
        'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming',
    ],

    'content' => [
        'TX' => [
            'heading' => 'HVAC services in Texas',
            'intro' => 'From humid Gulf Coast summers to dry West Texas heat and the occasional hard freeze, Texas puts heavy demands on heating and cooling systems. Request help with AC repair, replacement, heat pumps, and maintenance across the state.',
            'climate' => '<p>Air conditioning in much of Texas runs from spring well into fall, so systems log far more operating hours than in northern states. In Houston and along the coast, humidity is as much of a challenge as heat: systems need to remove moisture, and oversized equipment that cools quickly without dehumidifying leaves homes feeling clammy.</p><p>North and Central Texas see bigger temperature swings, with hot summers and winter cold snaps that can drop below freezing. The February 2021 winter storm showed how quickly heating systems and heat pumps can be pushed past their limits, and why backup heat and protected outdoor units matter.</p>',
            'body' => '<h2>Common HVAC needs in Texas</h2><ul><li>Spring AC tune-ups before peak cooling season</li><li>Condensate drain clogs from long, humid cooling seasons</li><li>Replacement of aging systems that still use R-22 refrigerant</li><li>Heat pump systems with auxiliary heat for winter cold snaps</li><li>Attic ductwork inspection — attic temperatures can far exceed outdoor temperatures in summer</li></ul>',
            'faqs' => [
                ['question' => 'When should Texas homeowners schedule AC maintenance?', 'answer' => 'Early spring, before temperatures climb. Because Texas cooling seasons are long, some homeowners also schedule a mid-season check in summer.'],
                ['question' => 'Do heat pumps work in Texas winters?', 'answer' => 'Yes for most of the state, where winters are mild. Systems should include auxiliary heat or a dual-fuel setup for the occasional hard freeze.'],
            ],
            'featured' => true,
        ],
        'CA' => [
            'heading' => 'HVAC services in California',
            'intro' => 'California\'s climates range from mild coastal cities to the hot Central Valley and inland deserts. Request help with air conditioning, heat pumps, ductless systems, and indoor air quality across the state.',
            'climate' => '<p>Coastal areas such as San Francisco and San Diego need relatively little cooling, while inland communities in the Central Valley, the Inland Empire, and the desert regions see long stretches of extreme summer heat. Many homes in milder coastal areas were built without central air, and ductless mini-splits and heat pumps have become a common way to add cooling.</p><p>Wildfire smoke is a recurring air quality concern across much of the state. Higher-efficiency filtration, sealed ductwork, and a system that can recirculate indoor air help reduce smoke infiltration on bad air days.</p>',
            'body' => '<h2>Common HVAC needs in California</h2><ul><li>Adding cooling to older homes without ductwork</li><li>Heat pump replacements for gas furnaces and AC systems</li><li>MERV 13 filtration upgrades for wildfire smoke</li><li>Title 24 energy code requirements for new equipment and duct testing</li></ul>',
            'faqs' => [
                ['question' => 'Does California require permits for HVAC replacement?', 'answer' => 'Generally yes. California\'s Title 24 energy standards apply to HVAC changeouts, and many jurisdictions require permits and duct leakage testing. Ask your installer to confirm.'],
                ['question' => 'How can my HVAC system help during wildfire smoke?', 'answer' => 'Use the highest-efficiency filter your system can handle (often MERV 13), keep windows closed, and run the fan to recirculate and filter indoor air. Portable air cleaners can help in bedrooms.'],
            ],
            'featured' => true,
        ],
        'FL' => [
            'heading' => 'HVAC services in Florida',
            'intro' => 'Florida\'s heat and humidity make air conditioning essential for most of the year. Request help with AC repair, replacement, humidity control, and maintenance across the state.',
            'climate' => '<p>Florida air conditioners run for most of the year, and controlling humidity is just as important as lowering temperature. High indoor humidity encourages mold, musty odors, and dust mites, so correctly sized systems, variable-speed equipment, and clean coils make a noticeable difference in comfort.</p><p>Salt air near the coast speeds up corrosion of outdoor coils and electrical connections. Coastal homeowners benefit from rinsing outdoor units regularly and asking about coated coils or corrosion-resistant equipment. Hurricane season also brings power outages and storm debris, so outdoor units should be secured and inspected after major storms.</p>',
            'body' => '<h2>Common HVAC needs in Florida</h2><ul><li>Condensate line clogs from near-constant cooling</li><li>Corrosion on coastal outdoor units</li><li>Dehumidification and indoor air quality upgrades</li><li>Post-storm equipment inspections</li></ul>',
            'faqs' => [
                ['question' => 'How often should AC systems be serviced in Florida?', 'answer' => 'Twice a year is common in Florida because systems run most of the year. At minimum, schedule an annual service before the hottest months.'],
                ['question' => 'What indoor humidity should I aim for in Florida?', 'answer' => 'Below about 50–55%. If your home stays above that with the AC running, a provider can check system sizing, airflow settings, or add whole-home dehumidification.'],
            ],
            'featured' => true,
        ],
        'NY' => [
            'heading' => 'HVAC services in New York',
            'intro' => 'New York homes need dependable heat through long winters and cooling through humid summers. Request help with furnaces, boilers, heat pumps, ductless systems, and air conditioning.',
            'climate' => '<p>Winters across New York are long and cold, especially upstate, where heating systems carry most of the annual load. Many older homes use boilers and radiators rather than forced air, so adding cooling often means ductless mini-splits or high-velocity systems instead of new ductwork.</p><p>Summers in New York City and the Hudson Valley are hot and humid. State programs have encouraged homeowners to switch to cold-climate heat pumps, and rebates for qualifying equipment have been available through utilities and state energy programs.</p>',
            'body' => '<h2>Common HVAC needs in New York</h2><ul><li>Fall heating tune-ups and boiler service</li><li>Ductless mini-splits for homes with radiators</li><li>Cold-climate heat pump installations</li><li>Window and through-wall unit replacement in apartments</li></ul>',
            'faqs' => [
                ['question' => 'Can I add central air to a home with radiators?', 'answer' => 'Yes. Ductless mini-splits and small-duct high-velocity systems are common choices because they avoid large new ducts. A provider can compare options for your layout.'],
                ['question' => 'Are heat pumps practical in upstate New York?', 'answer' => 'Cold-climate heat pumps are designed for sustained freezing temperatures, and many upstate homes use them, often with existing heating kept as backup for the coldest days.'],
            ],
            'featured' => true,
        ],
        'AZ' => [
            'heading' => 'HVAC services in Arizona',
            'intro' => 'Arizona\'s desert heat makes reliable air conditioning a necessity. Request help with AC repair, replacement, heat pumps, and maintenance across the state.',
            'climate' => '<p>In Phoenix, Tucson, and the low desert, summer highs regularly exceed 105°F and nighttime temperatures can stay above 90°F, so air conditioners run almost continuously for months. That workload shortens equipment life and makes capacitors, contactors, and fan motors common failure points.</p><p>Dust storms during monsoon season clog filters and outdoor coils quickly. Many Arizona homes use heat pumps, since winters in the low desert are mild, while higher-elevation communities such as Flagstaff have real winters and need dependable heating.</p>',
            'body' => '<h2>Common HVAC needs in Arizona</h2><ul><li>Spring AC tune-ups before extreme heat arrives</li><li>Frequent filter changes during monsoon dust storms</li><li>Capacitor and motor failures from heavy summer use</li><li>Heat pump replacement in low-desert homes</li></ul>',
            'faqs' => [
                ['question' => 'How often should I change my filter in Arizona?', 'answer' => 'Check it monthly during summer and after dust storms. Many homes need a new 1-inch filter every 30–60 days during peak season.'],
                ['question' => 'Why do AC capacitors fail so often in Arizona?', 'answer' => 'Extreme heat and long run times wear capacitors faster. A failing capacitor can keep the outdoor unit from starting, and it is one of the most common summer repairs in the desert.'],
            ],
            'featured' => true,
        ],
        'GA' => [
            'heading' => 'HVAC services in Georgia',
            'intro' => 'Georgia\'s hot, humid summers and cool winters call for systems that handle both. Request help with AC, heat pumps, furnaces, and indoor air quality across the state.',
            'climate' => '<p>Georgia summers are long, hot, and humid, especially in the Atlanta metro and coastal areas around Savannah. Humidity control matters for comfort and for preventing mold in crawl spaces and attics. Winters are generally mild, which makes heat pumps a popular choice, though North Georgia sees colder temperatures and occasional snow.</p><p>Pollen season in spring is intense across much of the state, making filtration and regular filter changes especially important for allergy sufferers.</p>',
            'body' => '<h2>Common HVAC needs in Georgia</h2><ul><li>Heat pump installation and repair</li><li>Crawl space and attic ductwork inspections</li><li>Spring filter upgrades for heavy pollen</li><li>Dual-fuel systems in North Georgia</li></ul>',
            'faqs' => [
                ['question' => 'Are heat pumps a good fit in Georgia?', 'answer' => 'For much of Georgia, yes. Mild winters let heat pumps heat efficiently most of the season. In colder North Georgia, a dual-fuel system with a gas furnace is a common choice.'],
                ['question' => 'How can I reduce pollen indoors?', 'answer' => 'Use a higher-rated filter your system can handle, change it more often during pollen season, keep windows closed, and make sure return ducts are sealed so they do not pull in attic or crawl space air.'],
            ],
            'featured' => true,
        ],
    ],

    // City pages. Each has its own local detail; none are created by swapping names into a template.
    'cities' => [
        'TX' => [
            'houston' => [
                'name' => 'Houston',
                'intro' => 'Houston\'s long, humid cooling season keeps air conditioners running from spring through late fall. Request help with AC repair, replacement, dehumidification, and maintenance in Houston and surrounding communities.',
                'local_info' => '<p>Houston\'s Gulf Coast location brings high humidity for much of the year, so comfort depends on removing moisture as well as heat. Correctly sized, variable-speed systems tend to dehumidify better than oversized single-stage units, and whole-home dehumidifiers are a common addition.</p><p>Many Houston-area homes have ductwork and air handlers in hot attics, where leaks and poor insulation waste cooling. Condensate drains clog frequently during the long cooling season, and flooding risk makes it worth raising outdoor units on elevated pads in flood-prone neighborhoods. After a major storm or flooding, have any equipment that was underwater inspected before running it.</p>',
                'faqs' => [
                    ['question' => 'Why does my Houston home feel humid even with the AC on?', 'answer' => 'An oversized system can cool the air quickly and shut off before removing much moisture. Fan settings, dirty coils, and leaky attic ducts can also contribute. A provider can check sizing and airflow, or recommend a dehumidifier.'],
                ],
                'featured' => true,
            ],
            'dallas' => [
                'name' => 'Dallas',
                'intro' => 'Dallas summers are hot, and North Texas winters can bring hard freezes. Request help with air conditioning, heat pumps, furnaces, and maintenance in Dallas and the surrounding metro.',
                'local_info' => '<p>Dallas–Fort Worth regularly sees summer stretches well above 100°F, which puts sustained stress on compressors, capacitors, and outdoor fan motors. At the same time, North Texas winters are colder than the Gulf Coast, with occasional ice storms and temperatures in the teens.</p><p>Many Dallas homes use a gas furnace for heat and a central air conditioner for cooling, while newer construction increasingly uses heat pumps. If you have a heat pump, make sure auxiliary heat is working before winter. Spring storms with hail can damage outdoor coil fins, so check the outdoor unit after severe weather.</p>',
                'faqs' => [
                    ['question' => 'Should I protect my outdoor unit before a freeze in Dallas?', 'answer' => 'Heat pumps should keep running in winter and should not be covered while in use. Clear snow and ice from around the unit. For AC-only units, a breathable top cover in winter is optional; never wrap the sides tightly.'],
                ],
                'featured' => true,
            ],
            'san-antonio' => [
                'name' => 'San Antonio',
                'intro' => 'San Antonio\'s hot summers and mild winters make reliable cooling the priority. Request help with AC repair, replacement, heat pumps, and tune-ups in San Antonio and nearby areas.',
                'local_info' => '<p>San Antonio sits between the humid Gulf Coast and the drier Hill Country, so homes face long cooling seasons with periods of both humid and dry heat. Air conditioners commonly run from April into October, making spring maintenance and filter changes especially important.</p><p>Winters are mild for most of the season, which makes heat pumps a practical choice, though occasional freezes mean backup heat should be in good working order. Older neighborhoods near downtown include historic homes where ductless mini-splits can add cooling without major changes to walls and ceilings.</p>',
                'faqs' => [
                    ['question' => 'Is a heat pump a good choice in San Antonio?', 'answer' => 'Usually, yes. Winters are mild enough for heat pumps to heat efficiently most of the time. Make sure the system includes auxiliary heat for occasional freezes.'],
                ],
                'featured' => false,
            ],
        ],
        'CA' => [
            'los-angeles' => [
                'name' => 'Los Angeles',
                'intro' => 'Los Angeles cooling needs vary from mild beach neighborhoods to hot inland valleys. Request help with air conditioning, heat pumps, ductless systems, and air quality across Los Angeles.',
                'local_info' => '<p>Neighborhoods near the coast often stay mild enough that many older homes were built without central air, while the San Fernando and San Gabriel Valleys regularly reach triple-digit temperatures in late summer and early fall. Heat waves in September and October can catch homes without cooling unprepared.</p><p>Ductless mini-splits and heat pumps are common ways to add cooling to older LA homes without major renovation. Wildfire smoke and urban air pollution make filtration important, and California\'s Title 24 energy rules mean most HVAC changeouts require a permit and duct testing.</p>',
                'faqs' => [
                    ['question' => 'What is the easiest way to add AC to an older Los Angeles home?', 'answer' => 'Ductless mini-splits are often the simplest option because they need only a small wall opening for each indoor unit. If the home already has ducts for a furnace, a heat pump or AC can sometimes use them.'],
                ],
                'featured' => true,
            ],
            'san-diego' => [
                'name' => 'San Diego',
                'intro' => 'San Diego\'s mild coastal climate turns hot further inland. Request help with heat pumps, ductless systems, air conditioning, and furnace service across San Diego County.',
                'local_info' => '<p>Coastal San Diego neighborhoods enjoy mild temperatures most of the year, so many homes rely on modest heating and little or no cooling. Inland communities such as El Cajon, Escondido, and Santee are much hotter in summer, and late-season heat waves can push temperatures well above normal even closer to the coast.</p><p>Because cooling needs are moderate in much of the county, heat pumps and ductless mini-splits are a popular, efficient way to cover both heating and cooling. Salt air near the ocean can corrode outdoor coils, so rinsing outdoor units and inspecting electrical connections is worthwhile for coastal homes.</p>',
                'faqs' => [
                    ['question' => 'Do I need air conditioning in San Diego?', 'answer' => 'It depends on how far inland you live. Many coastal homes manage with fans and good ventilation, while inland homes usually benefit from cooling. A heat pump can provide both heating and cooling in one system.'],
                ],
                'featured' => false,
            ],
        ],
        'FL' => [
            'miami' => [
                'name' => 'Miami',
                'intro' => 'Miami air conditioners run nearly year-round in heat and humidity. Request help with AC repair, replacement, dehumidification, and maintenance across Miami-Dade.',
                'local_info' => '<p>Miami\'s tropical climate means cooling systems rarely get a break, and humidity control is central to comfort and to preventing mold. Many condominiums and apartments use individual air handlers in closets or package units, while single-family homes commonly have split systems with outdoor condensers.</p><p>Salt air is hard on outdoor equipment near the coast, speeding up corrosion of coils and electrical parts. Hurricane season also brings power outages and debris; outdoor units should be secured to their pads, and equipment should be checked after major storms. Condominium owners should check their association\'s rules on equipment replacement and rooftop access.</p>',
                'faqs' => [
                    ['question' => 'How long do air conditioners last in Miami?', 'answer' => 'Often less than in milder climates because of near-constant use and salt air. Many systems last around 10–15 years; regular coil cleaning and maintenance help extend that.'],
                ],
                'featured' => true,
            ],
            'orlando' => [
                'name' => 'Orlando',
                'intro' => 'Orlando\'s hot, humid summers and frequent afternoon storms keep cooling systems busy. Request help with AC, heat pumps, and indoor air quality across the Orlando area.',
                'local_info' => '<p>Central Florida has fewer salt-air problems than the coasts but some of the state\'s most consistent summer heat and humidity, with afternoon thunderstorms most days of the rainy season. Lightning and power surges are a common cause of failed control boards and capacitors, so surge protection for HVAC equipment is worth discussing with a provider.</p><p>Many Orlando-area homes are newer construction with heat pumps, which handle the region\'s short, mild winters well. Long cooling seasons make condensate drain maintenance and regular filter changes essential.</p>',
                'faqs' => [
                    ['question' => 'Can lightning damage my AC in Orlando?', 'answer' => 'Yes. Nearby strikes and power surges can damage control boards, capacitors, and compressors. Ask a provider about a surge protector for the outdoor unit and air handler.'],
                ],
                'featured' => false,
            ],
            'tampa' => [
                'name' => 'Tampa',
                'intro' => 'Tampa Bay\'s humid subtropical climate demands dependable cooling and humidity control. Request help with AC repair, replacement, and maintenance across the Tampa area.',
                'local_info' => '<p>Tampa Bay sees long, humid summers with frequent afternoon storms, and homes near the bay and Gulf deal with salt air that corrodes outdoor units. Humidity control is a major comfort issue, and many older homes have ductwork in attics or ductwork that has been modified over the years.</p><p>The area\'s exposure to tropical storms and hurricanes makes it worth securing outdoor units and having equipment inspected after flooding. Heat pumps are common because winters are mild.</p>',
                'faqs' => [
                    ['question' => 'Should I have my AC checked after a hurricane in Tampa?', 'answer' => 'Yes, especially if the outdoor unit was submerged, hit by debris, or lost power during surges. Do not run equipment that was underwater until it has been inspected.'],
                ],
                'featured' => false,
            ],
        ],
        'NY' => [
            'new-york-city' => [
                'name' => 'New York City',
                'intro' => 'New York City homes need heat through cold winters and cooling through humid summers. Request help with heating, ductless systems, heat pumps, and air conditioning across the five boroughs.',
                'local_info' => '<p>Housing in New York City ranges from prewar apartment buildings with steam heat to brownstones, row houses, and detached homes in Queens and Staten Island. Many apartments rely on building-supplied heat and window or through-wall air conditioners, while homeowners increasingly install ductless mini-splits and heat pumps.</p><p>Installation in the city often involves building management approval, co-op or condo board rules, and limits on where outdoor units can be mounted. Have any rules for your building on hand when you request service.</p>',
                'faqs' => [
                    ['question' => 'Can I install a mini-split in a New York City apartment?', 'answer' => 'Often, yes, but you usually need approval from building management or your co-op or condo board, and there may be rules about where the outdoor unit can go. A provider can help with the technical requirements.'],
                ],
                'featured' => true,
            ],
        ],
        'AZ' => [
            'phoenix' => [
                'name' => 'Phoenix',
                'intro' => 'Phoenix summers are among the hottest in the country, and air conditioning is essential. Request help with AC repair, replacement, heat pumps, and tune-ups across the Valley.',
                'local_info' => '<p>Phoenix regularly sees summer highs above 110°F, and overnight lows can stay above 90°F, giving air conditioners little rest. Equipment in the Valley works harder than in almost any other U.S. market, so capacitors, contactors, and condenser fan motors fail more often, and systems may wear out sooner.</p><p>Monsoon season brings dust storms that clog filters and outdoor coils, so checking and changing filters after storms helps maintain airflow. Many homes have rooftop package units or attic air handlers exposed to intense heat. Heat pumps are common because winters are mild.</p>',
                'faqs' => [
                    ['question' => 'What temperature should my AC keep my Phoenix home at in summer?', 'answer' => 'Many systems are designed to hold a home about 20 degrees below the outdoor temperature. On 115°F days, a correctly working system may not reach very low settings. Setting the thermostat in the high 70s reduces strain.'],
                ],
                'featured' => true,
            ],
        ],
        'GA' => [
            'atlanta' => [
                'name' => 'Atlanta',
                'intro' => 'Atlanta\'s hot, humid summers, cool winters, and heavy spring pollen all affect home comfort. Request help with AC, heat pumps, furnaces, and air quality across metro Atlanta.',
                'local_info' => '<p>Metro Atlanta summers are hot and humid, and winters bring enough cold to make heating a real need, with occasional ice storms. Many homes use heat pumps, while others pair a gas furnace with a central air conditioner. Two-story homes commonly have a separate system or zoning for the upstairs, which tends to run warmer.</p><p>Atlanta\'s spring pollen counts are among the highest in the country, coating outdoor units and clogging filters. Changing filters more often in spring and sealing return ducts in attics and crawl spaces helps keep pollen out of the living space.</p>',
                'faqs' => [
                    ['question' => 'Why is my upstairs hotter than downstairs in Atlanta?', 'answer' => 'Heat rises, and upstairs rooms are closer to hot attics. Zoning, a second system, better attic insulation, or balancing the ductwork can help.'],
                ],
                'featured' => true,
            ],
        ],
    ],
];
