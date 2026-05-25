<?php

declare(strict_types=1);

namespace Phuture\Coherence\Enum;

/**
 * Enumeration for password hashing algorithm selection.
 *
 * This enum defines the password hashing algorithms available when hashing or
 * rehashing passwords. Use it with \Phuture\Coherence\Hash::password() and
 * \Phuture\Coherence\Hash::passwordNeedsRehash() to select which algorithm to apply.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
enum PasswordAlgorithm
{
    /**
     * Use the Argon2I algorithm.
     *
     * Argon2I is a memory-hard algorithm optimized to resist side-channel attacks.
     * It is the winner of the 2015 Password Hashing Competition. Use this when
     * side-channel resistance is the primary concern. Requires PHP compiled with
     * libargon2 support.
     */
    case Argon2i;

    /**
     * Use the Argon2ID algorithm.
     *
     * Argon2ID is a memory-hard algorithm that provides resistance against GPU-based
     * brute-force attacks and side-channel attacks. It is the recommended choice for
     * new applications when available. Requires PHP compiled with libargon2 support.
     */
    case Argon2id;

    /**
     * Use the BCrypt algorithm.
     *
     * BCrypt is a well-established password hashing algorithm that is widely supported
     * and produces a 60-character hash. It is a safe choice when Argon2ID is not available.
     */
    case Bcrypt;

    /**
     * Use PHP's current recommended default algorithm.
     *
     * Delegates the algorithm choice to PHP itself, which always points to the most
     * secure algorithm available in the current PHP version. This is the safest long-term
     * choice as it automatically upgrades when PHP changes its recommended default.
     */
    case Default;
}
