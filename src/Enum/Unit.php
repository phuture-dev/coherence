<?php

declare(strict_types=1);

namespace Phuture\Coherence\Enum;

/**
 * Enumeration of all supported measurement units for unit conversion.
 *
 * Each case represents a specific unit of measurement. Use these cases with
 * \Phuture\Coherence\Numbers::convert() to convert a value from one unit to another
 * within the same measurement category (for example, from Celsius to Fahrenheit,
 * or from Kilometer to Mile).
 *
 * The units are organized into the following categories:
 *
 * - **Temperature**: Celsius, Fahrenheit, Kelvin, Rankine
 * - **Distance**: Meter, Millimeter, Centimeter, Decimeter, Kilometer, Inch, Foot, Yard, Mile, NauticalMile
 * - **Mass**: Kilogram, Gram, Milligram, Microgram, MetricTon, Pound, Ounce, Stone, UsTon, ImperialTon
 * - **Volume**: Liter, Milliliter, CubicMeter, GallonUs, QuartUs, PintUs, CupUs, FluidOunceUs, Tablespoon, Teaspoon
 * - **Time**: Second, Millisecond, Microsecond, Nanosecond, Minute, Hour, Day, Week
 * - **Area**: SquareMeter, SquareKilometer, Hectare, Acre, SquareFoot, SquareYard, SquareMile, SquareInch
 * - **Speed**: MeterPerSecond, KilometerPerHour, MilePerHour, Knot, FootPerSecond
 * - **Pressure**: Pascal, Kilopascal, Bar, Millibar, Atmosphere, Psi, Mmhg
 * - **Energy**: Joule, Kilojoule, Calorie, Kilocalorie, WattHour, KilowattHour, Btu, Electronvolt
 * - **Power**: Watt, Kilowatt, Megawatt, HorsepowerMechanical, HorsepowerMetric
 * - **Force**: Newton, Kilonewton, Dyne, PoundForce, KilogramForce
 * - **Electric Potential**: Volt, Millivolt, Kilovolt, Megavolt
 * - **Electric Current**: Ampere, Milliampere, Microampere, Kiloampere
 * - **Luminous Intensity**: Candela, Millicandela, Kilocandela
 *
 * Example:
 * ```php
 * use Phuture\Coherence\Enum\Unit;
 * use Phuture\Coherence\Numbers;
 *
 * $fahrenheit = Numbers::convert(100, Unit::Celsius, Unit::Fahrenheit);
 * // Returns: '212.0000000000'
 *
 * $miles = Numbers::convert(1, Unit::Kilometer, Unit::Mile);
 * // Returns: '0.6213711922'
 * ```
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
enum Unit
{
    /**
     * Returns the measurement category this unit belongs to.
     *
     * Each unit belongs to exactly one category such as 'temperature', 'distance', or 'mass'.
     * Conversions are only valid between units in the same category.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Enum\Unit;
     *
     * Unit::Celsius->category(); // 'temperature'
     * Unit::Kilometer->category(); // 'distance'
     * Unit::Kilogram->category(); // 'mass'
     * ```
     *
     * @return string The category name
     */
    public function category(): string
    {
        return match ($this) {
            self::Celsius, self::Fahrenheit, self::Kelvin, self::Rankine => 'temperature',

            self::Meter, self::Millimeter, self::Centimeter, self::Decimeter,
            self::Kilometer, self::Inch, self::Foot, self::Yard,
            self::Mile, self::NauticalMile => 'distance',

            self::Kilogram, self::Gram, self::Milligram, self::Microgram,
            self::MetricTon, self::Pound, self::Ounce, self::Stone,
            self::UsTon, self::ImperialTon => 'mass',

            self::Liter, self::Milliliter, self::CubicMeter,
            self::GallonUs, self::QuartUs, self::PintUs, self::CupUs,
            self::FluidOunceUs, self::Tablespoon, self::Teaspoon => 'volume',

            self::Second, self::Millisecond, self::Microsecond, self::Nanosecond,
            self::Minute, self::Hour, self::Day, self::Week => 'time',

            self::SquareMeter, self::SquareKilometer, self::Hectare, self::Acre,
            self::SquareFoot, self::SquareYard, self::SquareMile,
            self::SquareInch => 'area',

            self::MeterPerSecond, self::KilometerPerHour, self::MilePerHour,
            self::Knot, self::FootPerSecond => 'speed',

            self::Pascal, self::Kilopascal, self::Bar, self::Millibar,
            self::Atmosphere, self::Psi, self::Mmhg => 'pressure',

            self::Joule, self::Kilojoule, self::Calorie, self::Kilocalorie,
            self::WattHour, self::KilowattHour, self::Btu,
            self::Electronvolt => 'energy',

            self::Watt, self::Kilowatt, self::Megawatt,
            self::HorsepowerMechanical, self::HorsepowerMetric => 'power',

            self::Newton, self::Kilonewton, self::Dyne,
            self::PoundForce, self::KilogramForce => 'force',

            self::Volt, self::Millivolt, self::Kilovolt,
            self::Megavolt => 'electric_potential',

            self::Ampere, self::Milliampere, self::Microampere,
            self::Kiloampere => 'electric_current',

            self::Candela, self::Millicandela,
            self::Kilocandela => 'luminous_intensity',
        };
    }

    /**
     * Returns the multiplication factor to convert this unit to the base unit of its category.
     *
     * The factor is a numeric string suitable for use with BCMath functions. Multiplying a value
     * in this unit by the factor produces the equivalent value in the base unit of the category.
     * For temperature units, this factor is not used because temperature conversion requires
     * offset-based formulas handled separately by \Phuture\Coherence\Numbers::convertTemperature().
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Enum\Unit;
     *
     * Unit::Kilometer->factor(); // '1000' (1 km = 1000 meters)
     * Unit::Gram->factor(); // '0.001' (1 g = 0.001 kilograms)
     * Unit::Hour->factor(); // '3600' (1 hour = 3600 seconds)
     * ```
     *
     * @return string The conversion factor as a BCMath-compatible numeric string
     */
    public function factor(): string
    {
        return match ($this) {
            self::Celsius => '1',
            self::Fahrenheit => '1',
            self::Kelvin => '1',
            self::Rankine => '1',

            self::Meter => '1',
            self::Millimeter => '0.001',
            self::Centimeter => '0.01',
            self::Decimeter => '0.1',
            self::Kilometer => '1000',
            self::Inch => '0.0254',
            self::Foot => '0.3048',
            self::Yard => '0.9144',
            self::Mile => '1609.344',
            self::NauticalMile => '1852',

            self::Kilogram => '1',
            self::Gram => '0.001',
            self::Milligram => '0.000001',
            self::Microgram => '0.000000001',
            self::MetricTon => '1000',
            self::Pound => '0.45359237',
            self::Ounce => '0.028349523125',
            self::Stone => '6.35029318',
            self::UsTon => '907.18474',
            self::ImperialTon => '1016.0469088',

            self::Liter => '1',
            self::Milliliter => '0.001',
            self::CubicMeter => '1000',
            self::GallonUs => '3.785411784',
            self::QuartUs => '0.946352946',
            self::PintUs => '0.473176473',
            self::CupUs => '0.2365882365',
            self::FluidOunceUs => '0.0295735295625',
            self::Tablespoon => '0.01478676478125',
            self::Teaspoon => '0.00492892159375',

            self::Second => '1',
            self::Millisecond => '0.001',
            self::Microsecond => '0.000001',
            self::Nanosecond => '0.000000001',
            self::Minute => '60',
            self::Hour => '3600',
            self::Day => '86400',
            self::Week => '604800',

            self::SquareMeter => '1',
            self::SquareKilometer => '1000000',
            self::Hectare => '10000',
            self::Acre => '4046.8564224',
            self::SquareFoot => '0.09290304',
            self::SquareYard => '0.83612736',
            self::SquareMile => '2589988.110336',
            self::SquareInch => '0.00064516',

            self::MeterPerSecond => '1',
            self::KilometerPerHour => '0.2777777778',
            self::MilePerHour => '0.44704',
            self::Knot => '0.5144444444',
            self::FootPerSecond => '0.3048',

            self::Pascal => '1',
            self::Kilopascal => '1000',
            self::Bar => '100000',
            self::Millibar => '100',
            self::Atmosphere => '101325',
            self::Psi => '6894.757293168',
            self::Mmhg => '133.3223684211',

            self::Joule => '1',
            self::Kilojoule => '1000',
            self::Calorie => '4.184',
            self::Kilocalorie => '4184',
            self::WattHour => '3600',
            self::KilowattHour => '3600000',
            self::Btu => '1055.06',
            self::Electronvolt => '0.0000000000000000001602176634',

            self::Watt => '1',
            self::Kilowatt => '1000',
            self::Megawatt => '1000000',
            self::HorsepowerMechanical => '745.7',
            self::HorsepowerMetric => '735.499',

            self::Newton => '1',
            self::Kilonewton => '1000',
            self::Dyne => '0.00001',
            self::PoundForce => '4.4482216152605',
            self::KilogramForce => '9.80665',

            self::Volt => '1',
            self::Millivolt => '0.001',
            self::Kilovolt => '1000',
            self::Megavolt => '1000000',

            self::Ampere => '1',
            self::Milliampere => '0.001',
            self::Microampere => '0.000001',
            self::Kiloampere => '1000',

            self::Candela => '1',
            self::Millicandela => '0.001',
            self::Kilocandela => '1000',
        };
    }

    /**
     * Acre, an imperial unit of area equal to approximately 4046.8564224 square meters.
     */
    case Acre;

    /**
     * Ampere, the base SI unit of electric current.
     */
    case Ampere;

    /**
     * Standard atmosphere, a reference pressure equal to 101325 pascals.
     */
    case Atmosphere;

    /**
     * Bar, a metric unit of pressure equal to 100000 pascals.
     */
    case Bar;

    /**
     * British thermal unit (BTU), approximately 1055.06 joules.
     */
    case Btu;

    /**
     * Calorie (thermochemical), approximately 4.184 joules.
     */
    case Calorie;

    /**
     * Candela, the base SI unit of luminous intensity.
     */
    case Candela;

    /**
     * Degrees Celsius, the standard metric temperature scale where water freezes at 0 and boils at 100.
     */
    case Celsius;

    /**
     * Centimeter, one hundredth of a meter (0.01 meters).
     */
    case Centimeter;

    /**
     * Cubic meter, a metric unit of volume equal to 1000 liters.
     */
    case CubicMeter;

    /**
     * US cup, one sixteenth of a US gallon (approximately 0.2365882365 liters).
     */
    case CupUs;

    /**
     * Day, 86400 seconds (24 hours).
     */
    case Day;

    /**
     * Decimeter, one tenth of a meter (0.1 meters).
     */
    case Decimeter;

    /**
     * Dyne, a CGS unit of force equal to 0.00001 newtons.
     */
    case Dyne;

    /**
     * Electronvolt, a tiny unit of energy used in particle physics (approximately 1.602e-19 joules).
     */
    case Electronvolt;

    /**
     * Degrees Fahrenheit, the temperature scale where water freezes at 32 and boils at 212.
     */
    case Fahrenheit;

    /**
     * US fluid ounce, a US unit of volume equal to approximately 0.029573529 liters.
     */
    case FluidOunceUs;

    /**
     * Foot, an imperial unit of length equal to exactly 12 inches (0.3048 meters).
     */
    case Foot;

    /**
     * Feet per second, an imperial speed unit equal to exactly 0.3048 meters per second.
     */
    case FootPerSecond;

    /**
     * US gallon, a US customary unit of volume equal to approximately 3.785411784 liters.
     */
    case GallonUs;

    /**
     * Gram, one thousandth of a kilogram (0.001 kilograms).
     */
    case Gram;

    /**
     * Hectare, a metric unit of area equal to 10000 square meters.
     */
    case Hectare;

    /**
     * Mechanical horsepower, an imperial power unit equal to approximately 745.7 watts.
     */
    case HorsepowerMechanical;

    /**
     * Metric horsepower (PS), equal to approximately 735.499 watts.
     */
    case HorsepowerMetric;

    /**
     * Hour, 3600 seconds (60 minutes).
     */
    case Hour;

    /**
     * Imperial ton (long ton), equal to 2240 pounds (approximately 1016.0469088 kilograms).
     */
    case ImperialTon;

    /**
     * Inch, an imperial unit of length equal to exactly 0.0254 meters.
     */
    case Inch;

    /**
     * Joule, the base SI unit of energy.
     */
    case Joule;

    /**
     * Kelvin, the absolute temperature scale starting at absolute zero (-273.15 degrees Celsius).
     */
    case Kelvin;

    /**
     * Kiloampere, one thousand amperes.
     */
    case Kiloampere;

    /**
     * Kilocalorie (food calorie), approximately 4184 joules.
     */
    case Kilocalorie;

    /**
     * Kilocandela, one thousand candelas.
     */
    case Kilocandela;

    /**
     * Kilogram, the base unit of mass in the metric system.
     */
    case Kilogram;

    /**
     * Kilogram-force, a gravitational metric unit of force equal to approximately 9.80665 newtons.
     */
    case KilogramForce;

    /**
     * Kilojoule, one thousand joules.
     */
    case Kilojoule;

    /**
     * Kilometer, one thousand meters (1000 meters).
     */
    case Kilometer;

    /**
     * Kilometers per hour, a metric speed unit equal to approximately 0.277778 meters per second.
     */
    case KilometerPerHour;

    /**
     * Kilonewton, one thousand newtons.
     */
    case Kilonewton;

    /**
     * Kilopascal, one thousand pascals.
     */
    case Kilopascal;

    /**
     * Kilovolt, one thousand volts.
     */
    case Kilovolt;

    /**
     * Kilowatt, one thousand watts.
     */
    case Kilowatt;

    /**
     * Kilowatt-hour, a unit of energy equal to 3600000 joules.
     */
    case KilowattHour;

    /**
     * Knot, a speed unit equal to one nautical mile per hour (approximately 0.514444 meters per second).
     */
    case Knot;

    /**
     * Liter, the base metric unit of volume.
     */
    case Liter;

    /**
     * Megavolt, one million volts.
     */
    case Megavolt;

    /**
     * Megawatt, one million watts.
     */
    case Megawatt;

    /**
     * Meter, the base unit of length in the metric system.
     */
    case Meter;

    /**
     * Meters per second, the base metric unit of speed.
     */
    case MeterPerSecond;

    /**
     * Metric ton, one thousand kilograms.
     */
    case MetricTon;

    /**
     * Microampere, one millionth of an ampere (0.000001 amperes).
     */
    case Microampere;

    /**
     * Microgram, one millionth of a gram (0.000000001 kilograms).
     */
    case Microgram;

    /**
     * Microsecond, one millionth of a second (0.000001 seconds).
     */
    case Microsecond;

    /**
     * Mile, an imperial unit of distance equal to 5280 feet (1609.344 meters).
     */
    case Mile;

    /**
     * Miles per hour, an imperial speed unit equal to approximately 0.44704 meters per second.
     */
    case MilePerHour;

    /**
     * Milliampere, one thousandth of an ampere (0.001 amperes).
     */
    case Milliampere;

    /**
     * Millibar, one thousandth of a bar (100 pascals).
     */
    case Millibar;

    /**
     * Millicandela, one thousandth of a candela (0.001 candelas).
     */
    case Millicandela;

    /**
     * Milligram, one thousandth of a gram (0.000001 kilograms).
     */
    case Milligram;

    /**
     * Milliliter, one thousandth of a liter (0.001 liters).
     */
    case Milliliter;

    /**
     * Millimeter, one thousandth of a meter (0.001 meters).
     */
    case Millimeter;

    /**
     * Millisecond, one thousandth of a second (0.001 seconds).
     */
    case Millisecond;

    /**
     * Millivolt, one thousandth of a volt (0.001 volts).
     */
    case Millivolt;

    /**
     * Minute, 60 seconds.
     */
    case Minute;

    /**
     * Millimeters of mercury (mmHg), a pressure unit equal to approximately 133.322 pascals.
     */
    case Mmhg;

    /**
     * Nanosecond, one billionth of a second (0.000000001 seconds).
     */
    case Nanosecond;

    /**
     * Nautical mile, a unit used in air and sea navigation equal to exactly 1852 meters.
     */
    case NauticalMile;

    /**
     * Newton, the base SI unit of force.
     */
    case Newton;

    /**
     * Ounce, an imperial unit of mass equal to approximately 0.028349523 kilograms (1/16 of a pound).
     */
    case Ounce;

    /**
     * Pascal, the base SI unit of pressure (one newton per square meter).
     */
    case Pascal;

    /**
     * US pint, one eighth of a US gallon (approximately 0.473176473 liters).
     */
    case PintUs;

    /**
     * Pound, an imperial unit of mass equal to approximately 0.45359237 kilograms.
     */
    case Pound;

    /**
     * Pound-force, an imperial unit of force equal to approximately 4.448222 newtons.
     */
    case PoundForce;

    /**
     * Pounds per square inch (PSI), an imperial pressure unit equal to approximately 6894.757 pascals.
     */
    case Psi;

    /**
     * US quart, one fourth of a US gallon (approximately 0.946352946 liters).
     */
    case QuartUs;

    /**
     * Degrees Rankine, an absolute temperature scale where zero is absolute zero and each degree
     * equals one degree Fahrenheit.
     */
    case Rankine;

    /**
     * Second, the base unit of time.
     */
    case Second;

    /**
     * Square foot, an imperial unit of area equal to approximately 0.09290304 square meters.
     */
    case SquareFoot;

    /**
     * Square inch, an imperial unit of area equal to approximately 0.00064516 square meters.
     */
    case SquareInch;

    /**
     * Square kilometer, one million square meters.
     */
    case SquareKilometer;

    /**
     * Square meter, the base metric unit of area.
     */
    case SquareMeter;

    /**
     * Square mile, an imperial unit of area equal to approximately 2589988.110336 square meters.
     */
    case SquareMile;

    /**
     * Square yard, an imperial unit of area equal to approximately 0.83612736 square meters.
     */
    case SquareYard;

    /**
     * Stone, an imperial unit of mass equal to 14 pounds (approximately 6.350293 kilograms).
     */
    case Stone;

    /**
     * Tablespoon, a culinary unit of volume equal to approximately 0.014786764 liters.
     */
    case Tablespoon;

    /**
     * Teaspoon, a culinary unit of volume equal to approximately 0.004928921 liters.
     */
    case Teaspoon;

    /**
     * US ton (short ton), equal to 2000 pounds (approximately 907.18474 kilograms).
     */
    case UsTon;

    /**
     * Volt, the base SI unit of electric potential.
     */
    case Volt;

    /**
     * Watt, the base SI unit of power (one joule per second).
     */
    case Watt;

    /**
     * Watt-hour, a unit of energy equal to 3600 joules.
     */
    case WattHour;

    /**
     * Week, 604800 seconds (7 days).
     */
    case Week;

    /**
     * Yard, an imperial unit of length equal to exactly 3 feet (0.9144 meters).
     */
    case Yard;
}
