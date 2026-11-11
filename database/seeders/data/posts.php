<?php

/*
| Launch articles for the resource center. Editable in Admin → Blog.
| $callout(variant, title, text) produces an editor-compatible callout block.
*/

$callout = fn (string $variant, string $title, string $text) => '<div data-type="customBlock" data-config="'
    .e(json_encode(['variant' => $variant, 'title' => $title, 'text' => $text])).'" data-id="callout"></div>';

return [
    [
        'slug' => 'why-is-my-ac-running-but-not-cooling',
        'title' => 'Why Is My AC Running but Not Cooling?',
        'category' => 'cooling',
        'tags' => ['troubleshooting', 'air conditioning'],
        'services' => ['ac-repair', 'hvac-maintenance'],
        'image' => 'technician-repairing-ac-condenser.jpg',
        'featured' => true,
        'days_ago' => 6,
        'primary_keyword' => 'ac running but not cooling',
        'secondary_keywords' => 'ac blowing warm air, air conditioner not cooling house',
        'excerpt' => 'If your air conditioner is running but the house keeps getting warmer, the cause is usually airflow, refrigerant, or the outdoor unit. Here is how to narrow it down safely.',
        'body' => '<p>Few things are more frustrating on a hot day than hearing the air conditioner run while the house keeps getting warmer. The good news is that the cause usually falls into one of a handful of categories, and a few of them are simple enough to check yourself.</p>'
            .'<h2>Start with the thermostat</h2><p>Confirm the thermostat is set to <strong>Cool</strong>, the set temperature is below the current room temperature, and the fan is set to <strong>Auto</strong>. If the fan is set to <strong>On</strong>, it will keep blowing air even when the system is not actively cooling, which can feel like warm air from the vents. Replace the batteries if your thermostat uses them.</p>'
            .'<h2>Check the air filter</h2><p>A clogged filter is one of the most common causes of poor cooling. It restricts airflow across the indoor coil, which reduces cooling and can eventually cause the coil to freeze. Pull the filter out and hold it up to a light. If you cannot see light through it, replace it.</p>'
            .$callout('tip', 'Quick check', 'Write the filter size printed on its frame on a piece of tape near the filter slot. Buying the right replacement becomes much easier.')
            .'<h2>Look at the outdoor unit</h2><p>Go outside while the system is running. You should hear the compressor humming and see the fan on top spinning. Then check:</p><ul><li><strong>Is the fan spinning?</strong> If the unit hums but the fan does not turn, a failed capacitor or fan motor is likely.</li><li><strong>Is the coil clean?</strong> The metal fins around the unit need to release heat. Grass clippings, leaves, and dirt act like a blanket.</li><li><strong>Is anything blocking it?</strong> Keep about two feet of clearance around the unit.</li></ul><p>If the outdoor unit is completely silent, check the breaker and the disconnect switch mounted on the wall near the unit.</p>'
            .'<h2>Look for ice</h2><p>Frost or ice on the copper refrigerant lines or the indoor coil means the coil is too cold, usually because of low airflow or low refrigerant. Turn the system off and set the fan to <strong>On</strong> to help it thaw, which can take several hours. Running a frozen system can damage the compressor.</p>'
            .$callout('safety', 'Do not add refrigerant yourself', 'Refrigerant can only be handled by EPA Section 608 certified technicians. A system that is low on refrigerant has a leak, and the leak needs to be found and repaired.')
            .'<h2>Problems that need a technician</h2><p>If the filter, thermostat, and outdoor unit all check out, the problem is likely one of these:</p><ul><li><strong>Low refrigerant</strong> from a leak in the coil or line set</li><li><strong>Failed capacitor or contactor</strong>, which keeps the compressor or fan from starting</li><li><strong>Compressor problems</strong>, usually the most expensive repair</li><li><strong>Leaky or disconnected ductwork</strong> dumping cool air into an attic or crawl space</li><li><strong>Undersized system</strong> that cannot keep up in extreme heat</li></ul>'
            .'<h2>What is normal on very hot days?</h2><p>Most residential systems are designed to keep the indoor temperature roughly 15–20 degrees below the outdoor temperature. On a 105°F afternoon, a working system may struggle to hold 72°F. If the system runs constantly but the house stays within that range, it may simply be at its design limit. If the gap is much smaller than that, have it checked.</p>',
        'faqs' => [
            ['question' => 'How long should my AC run to cool the house?', 'answer' => 'On a typical warm day, a correctly sized system runs in cycles of about 15–20 minutes. On extremely hot days, it may run almost continuously. Constant running with no temperature drop points to a problem.'],
            ['question' => 'Can a dirty filter really stop my AC from cooling?', 'answer' => 'Yes. Severely restricted airflow reduces cooling and can freeze the indoor coil, which stops cooling almost entirely until it thaws.'],
        ],
    ],
    [
        'slug' => 'signs-your-furnace-needs-repair',
        'title' => 'Common Signs Your Furnace Needs Repair',
        'category' => 'heating',
        'tags' => ['furnace', 'troubleshooting'],
        'services' => ['furnace-repair', 'heating-repair'],
        'image' => 'high-efficiency-gas-furnace.jpg',
        'featured' => false,
        'days_ago' => 13,
        'primary_keyword' => 'signs furnace needs repair',
        'secondary_keywords' => 'furnace problems, furnace not heating',
        'excerpt' => 'Strange noises, short cycling, rising bills, and a yellow flame are all signs your furnace needs attention. Learn what each one means and which ones are urgent.',
        'body' => '<p>Furnaces usually give warning signs before they fail. Catching those signs early can mean a smaller repair, fewer cold nights, and safer operation.</p>'
            .$callout('safety', 'If you smell gas', 'Leave the house immediately without flipping switches or using phones inside. Call 911 or your gas utility from outside.')
            .'<h2>1. Unusual noises</h2><p>Some sound is normal, but new noises deserve attention:</p><ul><li><strong>Loud bang at startup:</strong> often delayed ignition, where gas builds up before lighting. This should be inspected soon.</li><li><strong>Squealing:</strong> a worn blower motor bearing or belt.</li><li><strong>Rattling:</strong> loose panels, ductwork, or a failing inducer motor.</li><li><strong>Repeated clicking without lighting:</strong> an ignition problem.</li></ul>'
            .'<h2>2. Short cycling</h2><p>A furnace that turns on and shuts off every few minutes is wearing itself out. Common causes include a dirty flame sensor, an overheating furnace from a clogged filter, or a thermostat placed too close to a heat source.</p>'
            .'<h2>3. Yellow or flickering flame</h2><p>Gas burners should burn with a steady blue flame. A yellow, orange, or lazy flame can indicate incomplete combustion, which can produce carbon monoxide. Have it inspected promptly.</p>'
            .'<h2>4. Rising heating bills</h2><p>If your bills climb without a change in weather or thermostat settings, the furnace may be losing efficiency from dirty burners, a failing blower, or leaky ductwork.</p>'
            .'<h2>5. Uneven heat or cold rooms</h2><p>Rooms that used to be comfortable but are now cold may point to blower problems, closed or blocked vents, or duct leaks.</p>'
            .'<h2>6. Error codes</h2><p>Most furnaces built in the last 20 years have a control board with a status light. A flashing pattern corresponds to a fault code, explained on a label inside the furnace door. Note the code before you call.</p>'
            .$callout('key', 'Carbon monoxide alarms', 'Install carbon monoxide alarms on every level of your home and near bedrooms. Test them monthly and replace them according to the manufacturer\'s schedule.')
            .'<h2>Repair or replace?</h2><p>Furnaces commonly last 15–20 years. If your furnace is near that age and needs a major repair such as a heat exchanger or control board, compare the repair cost with a new high-efficiency system before deciding.</p>',
        'faqs' => [
            ['question' => 'Is it safe to run a furnace that is making a banging noise?', 'answer' => 'A loud bang when the burners light can indicate delayed ignition, which stresses the heat exchanger. Have it inspected before continuing to use it regularly.'],
        ],
    ],
    [
        'slug' => 'ac-repair-vs-replacement',
        'title' => 'AC Repair vs. Replacement: How to Decide',
        'category' => 'buying-guides',
        'tags' => ['air conditioning', 'replacement'],
        'services' => ['ac-replacement', 'ac-repair'],
        'image' => 'technician-reviewing-options-with-customer.jpg',
        'featured' => false,
        'days_ago' => 20,
        'primary_keyword' => 'ac repair vs replacement',
        'secondary_keywords' => 'when to replace air conditioner, should i repair or replace my ac',
        'excerpt' => 'Age, repair cost, refrigerant type, and efficiency all factor into whether to repair your air conditioner or replace it. Use this framework before you decide.',
        'body' => '<p>When an air conditioner breaks down, the first question is often whether it is worth fixing. There is no single answer, but a few factors make the decision much clearer.</p>'
            .'<h2>Age of the system</h2><p>Central air conditioners commonly last 12 to 17 years. A repair on a five-year-old system is usually a clear yes. On a system in its mid-teens, even a moderate repair deserves a second look, because other parts may be close behind.</p>'
            .'<h2>The cost of the repair</h2><p>Small repairs such as a capacitor, contactor, or thermostat are inexpensive relative to a new system. Major repairs — a compressor, an evaporator coil, or a refrigerant leak in an old coil — can approach a significant share of replacement cost.</p>'
            .$callout('tip', 'The 5,000 rule', 'Multiply the system\'s age by the repair estimate. If the result is over 5,000, replacement is usually worth considering. It is a rule of thumb, not a verdict.')
            .'<h2>Refrigerant type</h2><p>Systems installed before about 2010 often use R-22 refrigerant, which is no longer produced in the United States. Only recovered and reclaimed R-22 remains, so recharging these systems is expensive. If an R-22 system has a leak, replacement is often the more economical path.</p>'
            .'<h2>Efficiency and operating cost</h2><p>A system installed 15 years ago may have been far less efficient than today\'s minimums, and efficiency drops further with age. A newer system can lower cooling costs, especially in hot climates where the AC runs most of the year. Utility rebates and federal tax credits for qualifying equipment can also offset part of the cost.</p>'
            .'<h2>Comfort problems</h2><p>If the system has never kept the house comfortable — hot upstairs rooms, high humidity, constant running — a replacement is a chance to fix those problems with correct sizing, variable-speed equipment, or zoning.</p>'
            .'<h2>Questions to ask the technician</h2><ul><li>What failed, and is anything else showing wear?</li><li>What refrigerant does the system use?</li><li>What would a replacement involve, and what size system does a load calculation support?</li><li>What warranties come with the repair versus a new system?</li></ul>',
        'faqs' => [
            ['question' => 'How long do central air conditioners last?', 'answer' => 'Typically 12–17 years, depending on climate, maintenance, and installation quality. Systems in very hot climates that run most of the year may wear out sooner.'],
            ['question' => 'Is it worth repairing an R-22 air conditioner?', 'answer' => 'For small repairs that do not involve refrigerant, it can be. For refrigerant leaks or compressor failures, replacement is often more economical because R-22 is expensive and increasingly scarce.'],
        ],
    ],
    [
        'slug' => 'how-often-should-you-change-hvac-filters',
        'title' => 'How Often Should You Change Your HVAC Filter?',
        'category' => 'maintenance',
        'tags' => ['filters', 'maintenance'],
        'services' => ['hvac-maintenance', 'indoor-air-quality'],
        'image' => 'smart-thermostat.jpg',
        'featured' => false,
        'days_ago' => 27,
        'primary_keyword' => 'how often to change hvac filter',
        'secondary_keywords' => 'air filter replacement, merv rating',
        'excerpt' => 'Most 1-inch filters need replacing every one to three months, but pets, allergies, construction, and filter type all change the schedule.',
        'body' => '<p>Changing the air filter is the simplest and least expensive thing you can do for your heating and cooling system. A clean filter protects the equipment, keeps airflow strong, and improves indoor air quality.</p>'
            .'<h2>General guidelines</h2><ul><li><strong>1-inch filters:</strong> every 1–3 months</li><li><strong>2-inch filters:</strong> every 3–4 months</li><li><strong>4–5 inch media filters:</strong> every 6–12 months</li></ul><p>These are starting points. Check your filter monthly until you learn how quickly it gets dirty in your home.</p>'
            .'<h2>When to change it more often</h2><ul><li>You have pets, especially dogs or cats that shed</li><li>Someone in the home has allergies or asthma</li><li>You live in a dusty area or near construction</li><li>You are renovating or sanding</li><li>Wildfire smoke or heavy pollen is affecting your area</li><li>The system is running constantly in peak summer or winter</li></ul>'
            .$callout('tip', 'Set a reminder', 'Put a recurring reminder on your phone for the first of each month to check the filter. It takes less than a minute.')
            .'<h2>Understanding MERV ratings</h2><p>MERV (Minimum Efficiency Reporting Value) rates how well a filter captures particles, on a scale from 1 to 16 for most residential filters. MERV 8 catches dust and pollen; MERV 11–13 catches finer particles such as pet dander, smoke, and some bacteria.</p><p>Higher is not automatically better. A high-MERV 1-inch filter can restrict airflow in systems that were not designed for it, reducing efficiency and comfort. If you want better filtration, a deeper filter cabinet with a 4- or 5-inch media filter allows higher efficiency with less restriction.</p>'
            .'<h2>What happens if you do not change it?</h2><p>A clogged filter reduces airflow, which can freeze the AC coil in summer, overheat the furnace in winter, increase energy use, and shorten equipment life. Dust that bypasses a clogged filter also builds up on the indoor coil and blower, which is harder and more expensive to clean.</p>',
        'faqs' => [
            ['question' => 'Which direction does the filter go?', 'answer' => 'Filters have an arrow on the frame showing airflow direction. The arrow should point toward the furnace or air handler, away from the return duct.'],
            ['question' => 'Are washable filters a good idea?', 'answer' => 'Washable filters can work, but they usually capture less than disposable pleated filters and must be completely dry before reinstalling to avoid mold growth.'],
        ],
    ],
    [
        'slug' => 'what-affects-hvac-installation-costs',
        'title' => 'What Affects HVAC Installation Costs?',
        'category' => 'buying-guides',
        'tags' => ['installation', 'replacement'],
        'services' => ['ac-installation', 'furnace-installation', 'heat-pump-services'],
        'image' => 'technicians-condenser-installation.jpg',
        'featured' => false,
        'days_ago' => 34,
        'primary_keyword' => 'hvac installation cost factors',
        'secondary_keywords' => 'new hvac system cost, hvac quote comparison',
        'excerpt' => 'System type, size, efficiency, ductwork, electrical work, permits, and your home itself all shape what a new HVAC system costs. Here is what drives the price.',
        'body' => '<p>Quotes for a new heating and cooling system can vary widely, even for the same house. Understanding what goes into the price makes it easier to compare quotes fairly and spot what may be missing.</p>'
            .'<h2>Type of system</h2><p>A central air conditioner paired with an existing furnace, a heat pump, a dual-fuel system, and a multi-zone ductless setup all involve different equipment and labor. Replacing both heating and cooling at once costs more upfront than replacing one, but can save on labor compared with doing them separately.</p>'
            .'<h2>Size and efficiency</h2><p>Larger capacity costs more, which is one reason correct sizing matters. Efficiency also affects price: higher SEER2, HSPF2, and AFUE ratings cost more upfront and less to operate. Two-stage and variable-speed equipment adds cost and improves comfort.</p>'
            .'<h2>Ductwork</h2><p>Reusing ductwork in good condition keeps costs down. Leaky, undersized, or damaged ducts may need sealing, modification, or replacement — and adding ductwork to a home that has none is a significant project.</p>'
            .'<h2>Electrical and gas work</h2><p>Heat pumps and larger systems may need a new circuit or an electrical panel upgrade. Furnace replacements may require gas line or venting changes, especially when switching to a high-efficiency condensing furnace.</p>'
            .'<h2>Access and location</h2><p>Equipment in tight attics, crawl spaces, or rooftops takes more labor. Moving the outdoor unit or the indoor equipment to a new spot adds line set and electrical work.</p>'
            .'<h2>Permits, inspections, and removal</h2><p>Most jurisdictions require permits for HVAC replacement. A complete quote includes permits, inspections, haul-away of old equipment, and refrigerant recovery.</p>'
            .$callout('key', 'Compare quotes line by line', 'Ask each provider for equipment model numbers, efficiency ratings, a load calculation, what ductwork and electrical work is included, permit costs, and warranty terms. Price alone does not tell you whether two quotes are for the same work.')
            .'<h2>Rebates and incentives</h2><p>Utility rebates, state programs, and federal tax credits can reduce the net cost of qualifying high-efficiency equipment. Rules and amounts change, so confirm current programs with your utility and installer.</p>',
        'faqs' => [
            ['question' => 'Why are HVAC quotes so different from each other?', 'answer' => 'Quotes may differ in equipment brand and efficiency, system size, ductwork and electrical work included, warranty length, and whether permits are part of the price. Compare the details, not just the total.'],
        ],
    ],
    [
        'slug' => 'when-to-schedule-hvac-maintenance',
        'title' => 'When Should Homeowners Schedule HVAC Maintenance?',
        'category' => 'maintenance',
        'tags' => ['maintenance', 'seasonal'],
        'services' => ['hvac-maintenance'],
        'image' => 'technician-rooftop-equipment.jpg',
        'featured' => false,
        'days_ago' => 41,
        'primary_keyword' => 'when to schedule hvac maintenance',
        'secondary_keywords' => 'hvac tune up schedule, spring ac maintenance',
        'excerpt' => 'Spring for cooling, fall for heating, and twice a year for heat pumps. Here is how to time HVAC maintenance and what each visit should cover.',
        'body' => '<p>Preventive maintenance is most valuable when it happens before your system faces its toughest work. Timing visits to the seasons helps you avoid the busiest periods for providers and catch problems before they leave you without heat or cooling.</p>'
            .'<h2>Air conditioners: spring</h2><p>Schedule cooling maintenance in early to mid spring, before temperatures climb. Waiting until the first heat wave means competing with everyone whose system just failed.</p>'
            .'<h2>Furnaces and boilers: fall</h2><p>Heating service is best in early fall. Technicians clean burners, check ignition and safety controls, inspect the heat exchanger, and test for carbon monoxide.</p>'
            .'<h2>Heat pumps: spring and fall</h2><p>Heat pumps heat and cool, so they run most of the year. Two visits — one before cooling season and one before heating season — keep them performing well.</p>'
            .$callout('tip', 'Book early in the season', 'Providers are typically less busy in shoulder seasons, which can make it easier to find a convenient appointment.')
            .'<h2>What a cooling visit should include</h2><ul><li>Cleaning the outdoor condenser coil</li><li>Checking refrigerant pressures and temperatures</li><li>Testing capacitors, contactors, and electrical connections</li><li>Clearing the condensate drain</li><li>Checking airflow and thermostat operation</li></ul>'
            .'<h2>What a heating visit should include</h2><ul><li>Inspecting and cleaning burners and the flame sensor</li><li>Testing ignition, limit switches, and safety controls</li><li>Checking venting and combustion</li><li>Inspecting the heat exchanger</li><li>Checking blower operation and filter</li></ul>'
            .'<h2>Between visits</h2><p>Check your filter monthly, keep the outdoor unit clear of debris, and make sure vents are not blocked by furniture or rugs.</p>',
        'faqs' => [
            ['question' => 'Is annual HVAC maintenance really necessary?', 'answer' => 'Most manufacturers recommend it, and some warranties require it. Regular maintenance helps systems run efficiently and catches worn parts before they cause a breakdown.'],
        ],
    ],
    [
        'slug' => 'heat-pumps-vs-traditional-heating',
        'title' => 'Heat Pumps vs. Traditional Heating Systems',
        'category' => 'heating',
        'tags' => ['heat pumps', 'buying guide'],
        'services' => ['heat-pump-services', 'furnace-installation', 'mini-split-systems'],
        'image' => 'technician-outdoor-heat-pump.jpg',
        'featured' => false,
        'days_ago' => 48,
        'primary_keyword' => 'heat pump vs furnace',
        'secondary_keywords' => 'heat pump vs gas furnace, cold climate heat pump',
        'excerpt' => 'Heat pumps move heat instead of burning fuel. Compare how they stack up against gas furnaces, boilers, and electric resistance heat on cost, comfort, and climate.',
        'body' => '<p>Heat pumps have become one of the most discussed options for home heating. Whether one makes sense for you depends on your climate, fuel prices, existing equipment, and comfort preferences.</p>'
            .'<h2>How heat pumps work</h2><p>A heat pump uses refrigerant to move heat. In winter it pulls heat from outdoor air — even cold air contains heat — and moves it inside. In summer it reverses and works as an air conditioner. Because it moves heat rather than creating it, a heat pump can deliver more heat energy than the electricity it consumes.</p>'
            .'<h2>Heat pumps vs. gas furnaces</h2><ul><li><strong>Comfort:</strong> furnaces deliver hotter air in shorter bursts; heat pumps deliver warm air over longer, steadier cycles.</li><li><strong>Operating cost:</strong> depends on local electricity and gas prices. Where electricity is relatively affordable or gas is expensive, heat pumps often cost less to run.</li><li><strong>Cooling:</strong> a heat pump replaces the air conditioner too; a furnace still needs a separate AC.</li><li><strong>Cold weather:</strong> modern cold-climate heat pumps heat effectively well below freezing, but efficiency declines as temperatures drop.</li></ul>'
            .'<h2>Heat pumps vs. electric resistance heat</h2><p>Electric furnaces and baseboard heaters convert electricity directly to heat. Heat pumps typically use a fraction of the electricity for the same heat output, so replacing resistance heat is often one of the clearest upgrade cases.</p>'
            .'<h2>Heat pumps vs. boilers</h2><p>Boilers provide even, quiet radiant heat but no cooling. Homes with boilers often add ductless mini-split heat pumps for cooling and shoulder-season heating, keeping the boiler for the coldest weather.</p>'
            .$callout('key', 'Dual-fuel systems', 'A dual-fuel system pairs a heat pump with a gas furnace. The heat pump handles most of the season, and the furnace takes over in the coldest weather. It is a popular middle path in cold climates.')
            .'<h2>Questions to ask before switching</h2><ul><li>What is the heat pump\'s rated heating capacity at 5°F or 17°F?</li><li>Does my electrical panel have capacity for it?</li><li>Can my existing ductwork deliver the airflow it needs?</li><li>What backup heat will the system use, and when?</li><li>What rebates or tax credits currently apply?</li></ul>',
        'faqs' => [
            ['question' => 'Can a heat pump replace my furnace completely?', 'answer' => 'In many climates, yes, especially with a cold-climate model. In very cold regions, a backup heat source — electric strips or a furnace in a dual-fuel setup — is common for the coldest days.'],
            ['question' => 'Are heat pumps noisy?', 'answer' => 'Modern variable-speed heat pumps are generally quiet. During defrost cycles in winter, you may hear a brief whoosh and see steam from the outdoor unit, which is normal.'],
        ],
    ],
    [
        'slug' => 'common-hvac-problems-in-summer',
        'title' => 'Common HVAC Problems During Summer',
        'category' => 'cooling',
        'tags' => ['air conditioning', 'seasonal', 'troubleshooting'],
        'services' => ['ac-repair', 'hvac-maintenance', 'emergency-hvac'],
        'image' => 'technician-servicing-condenser.jpg',
        'featured' => false,
        'days_ago' => 55,
        'primary_keyword' => 'common summer hvac problems',
        'secondary_keywords' => 'ac problems in summer, air conditioner issues heat wave',
        'excerpt' => 'Heat waves expose weak capacitors, clogged drains, dirty coils, and tired compressors. Learn the most common summer AC problems and how to prevent them.',
        'body' => '<p>Summer is when air conditioners work hardest, and it is when small weaknesses turn into breakdowns. Knowing the most common problems helps you prevent some of them and recognize the rest early.</p>'
            .'<h2>Failed capacitors</h2><p>Capacitors give the compressor and fan motors the boost they need to start. Heat speeds up their wear, making them one of the most common summer repairs. A unit that hums but does not start, or a fan that will not spin, often points to a failed capacitor.</p>'
            .$callout('safety', 'Stay out of the electrical panel', 'Capacitors can hold a dangerous charge even when the power is off. Capacitor testing and replacement should be left to a technician.')
            .'<h2>Clogged condensate drains</h2><p>Your AC removes gallons of water from indoor air on humid days. Algae and debris can block the drain line, causing water to back up into the drain pan, trip a safety switch, or overflow into ceilings and floors.</p>'
            .'<h2>Frozen evaporator coils</h2><p>Low airflow from a dirty filter or blocked returns, or low refrigerant from a leak, can freeze the indoor coil. You may see ice on the refrigerant lines. Turn the system off and let it thaw before service.</p>'
            .'<h2>Dirty outdoor coils</h2><p>The outdoor coil releases heat removed from your home. Cottonwood fluff, grass clippings, and dirt reduce its ability to do that, making the system run longer and work harder in the heat.</p>'
            .'<h2>Refrigerant leaks</h2><p>Leaks reduce cooling capacity gradually. Signs include longer run times, warm air from vents, hissing sounds, and ice on lines. A technician needs to find and repair the leak before recharging.</p>'
            .'<h2>Thermostat issues</h2><p>Dead batteries, a thermostat in direct sun, or incorrect settings can all look like an AC problem. Check the thermostat first.</p>'
            .'<h2>How to prevent summer breakdowns</h2><ul><li>Schedule a spring tune-up before the first heat wave</li><li>Change the filter monthly during peak season</li><li>Keep two feet of clearance around the outdoor unit</li><li>Close blinds on sun-facing windows to reduce load</li><li>Avoid setting the thermostat extremely low on the hottest days</li></ul>',
        'faqs' => [
            ['question' => 'Why does my AC break down during heat waves?', 'answer' => 'Extreme heat makes the system run for long periods at full capacity, which exposes weak capacitors, motors, and compressors. Dirty coils and filters make the strain worse.'],
        ],
    ],
];
