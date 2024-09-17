<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $this->call(AreaSeeder::class);
         $this->call(Specialization_iconsSeeder::class);
         $this->call(NotificationTypesSeeder::class);
         $this->call(CountryAlliasSeeder::class);
         $this->call(UpdateMainSpecializationNameSeeder::class);
         $this->call(CountriesIconsAndCodeSeeder::class);
         $this->call(VitalsSeeder::class);
         $this->call(PatientVitalsSeeder::class);
         $this->call(CurrencySeeder::class);
         $this->call(updateAreasSeeder::class);
         $this->call(WhichLocationToBeShownSeeder::class);
         $this->call(HandleSpecialityAllSeeder::class);
         $this->call(updateLocationStatusWithDeledetedDoctorsSeeder::class);
         $this->call(handleDuplicatedAreasSeeder::class);
         $this->call(KenyaLocationSeeder::class);
         $this->call(DoctorBiographySeeder::class);
         $this->call(PaymentMethodsSeeder::class);
         $this->call(PaymentTransactionTypesSeeder::class);
         $this->call(PlanTypesSeeder::class);
         $this->call(CountrySubscriptionPolicySeeder::class);
         $this->call(updateNigeriaCitiesSeeder::class);
         $this->call(addNewCountriesSeeder::class);
    }
}
