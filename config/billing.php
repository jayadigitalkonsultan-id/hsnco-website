<?php
return ['fees'=>[
 ['name'=>'Hourly Rate','label'=>'Time-based','icon'=>'clock','description'=>'Fees are calculated by reference to the time spent by our lawyers on a matter.','basis'=>'Lawyer time'],
 ['name'=>'Flat Fee','label'=>'Fixed fee','icon'=>'document','description'=>'A fixed fee for specified services with a clearly defined scope.','basis'=>'Scope of services'],
 ['name'=>'Success Fee','label'=>'Agreed outcomes','icon'=>'target','description'=>'A fee payable upon achieving the client’s agreed legal objective, calculated as a percentage of the outcome or subject matter.','basis'=>'Outcome or subject matter'],
 ['name'=>'Retainer Fee','label'=>'Ongoing support','icon'=>'calendar','description'=>'A monthly fee for ongoing legal services, typically for corporate clients.','basis'=>'Monthly services'],
 ['name'=>'Contingency Fee','label'=>'Outcome-based','icon'=>'scales','description'=>'A fee payable only if a matter is successful or achieves a favourable outcome.','basis'=>'Case outcome'],
 ['name'=>'Project-Based Fee','label'=>'Project-specific','icon'=>'project','description'=>'A fee tailored to a specific legal project, such as a merger, acquisition or legal audit.','basis'=>'Scope of the legal project'],
]];