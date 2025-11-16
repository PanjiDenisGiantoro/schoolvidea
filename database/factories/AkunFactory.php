<?php

namespace Database\Factories;

use App\Models\Akun;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class AkunFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Akun::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_akun' => $this->faker->unique()->numerify('###.###.##'),
            'nama_akun' => $this->faker->sentence(3),
            'tipe' => $this->faker->randomElement(['ASET', 'LIABILITAS', 'EKUITAS', 'PENDAPATAN', 'BEBAN']),
            'unit_id' => Unit::factory(),
            'parent_id' => null,
            'status' => 1,
            'kategori_akun' => null,
            'keterangan' => $this->faker->sentence(),
        ];
    }

    /**
     * Create an akun with a specific unit
     */
    public function forUnit(Unit $unit): self
    {
        return $this->state(function (array $attributes) use ($unit) {
            return [
                'unit_id' => $unit->id,
            ];
        });
    }

    /**
     * Create an akun with a parent
     */
    public function withParent(Akun $parent): self
    {
        return $this->state(function (array $attributes) use ($parent) {
            return [
                'parent_id' => $parent->id,
                'unit_id' => $parent->unit_id,
            ];
        });
    }

    /**
     * Create an inactive akun
     */
    public function inactive(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 0,
            ];
        });
    }
}
