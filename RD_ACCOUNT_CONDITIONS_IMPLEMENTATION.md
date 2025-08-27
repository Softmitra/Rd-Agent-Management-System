# RD Account Conditions Implementation

This document summarizes the changes made to implement the RD account conditions as specified.

## Conditions Implemented

### 1. Minimum Installment and Deposit Multiples
- **Requirement**: Minimum installment – Rs. 100, higher deposits in multiples of Rs. 10
- **Implementation**: 
  - Added validation in `RDAccountController@store` with `min:100` and `multiple_of:10` rules
  - Added `validateMonthlyAmount()` method in `RdAccount` model
  - Updated form validation in create view with appropriate messaging

### 2. No Maximum Limit on Deposit Amount
- **Requirement**: No maximum limit on deposit amount
- **Implementation**: No changes needed (already supported)

### 3. Deposits in Cash/Cheque
- **Requirement**: Deposits can be made in cash/cheque
- **Implementation**:
  - Added `payment_method` enum field (cash/cheque)
  - Added cheque details fields: `cheque_number`, `cheque_date`, `cheque_bank`
  - Updated create form with payment method selection and conditional cheque fields
  - Added JavaScript to toggle cheque fields visibility

### 4. Nomination Facility Available
- **Requirement**: Nomination facility available
- **Implementation**:
  - Added nomination fields: `nominee_name`, `nominee_relation`, `nominee_phone`
  - Added `hasNomination()` method to check if nomination exists
  - Updated create form with nomination fields

### 5. Rebate on Advance Payment
- **Requirement**: Rebate of Rs. 10 for 6 months, Rs. 40 for 12 months
- **Implementation**:
  - Updated `calculateRebate()` method with new rebate structure
  - Updated `getRebateInfoAttribute()` to reflect new milestones
  - Changed from Rs. 50 every 6 months to specific amounts

### 6. Sole/Joint Operation (up to 3 adults)
- **Requirement**: Sole/joint (up to 3 adults) operation allowed
- **Implementation**:
  - Added `joint_holder_2_name` and `joint_holder_3_name` fields
  - Updated `getAllJointHoldersAttribute()` method to include all holders
  - Updated create form with additional joint holder fields

### 7. Interest Compounded Quarterly
- **Requirement**: Interest on RD compounded quarterly
- **Implementation**:
  - Added `interest_compounding` enum field (monthly, quarterly, yearly)
  - Set default to 'quarterly'

### 8. Default Fee for Missed Deposits
- **Requirement**: Default fee of Re. 1 for Rs. 100 denomination (proportional for others)
- **Implementation**:
  - Updated `getPenaltyPerMonthAttribute()` method
  - Changed from 1% of monthly deposit to fixed Re. 1 for Rs. 100, proportional calculation

### 9. Premature Closure After Three Years
- **Requirement**: Premature closure allowed after three years
- **Implementation**:
  - Added `premature_closure_date` and `premature_closure_amount` fields
  - Added `isEligibleForPrematureClosure()` method to check eligibility

### 10. Transferable Between Post Offices
- **Requirement**: Easily transferable from one post office to another
- **Implementation**:
  - Added `previous_post_office` and `transfer_date` fields
  - Added `isTransferred()` method to check if account was transferred

### 11. Loan Facility
- **Requirement**: Loan facility up to 50% of balance credit after 12 installments and 1 year
- **Implementation**:
  - Added loan fields: `loan_availed`, `loan_amount`, `loan_date`, `loan_repayment_date`
  - Added `isEligibleForLoan()` method to check eligibility
  - Added `getMaxLoanAmountAttribute()` method to calculate maximum loan amount (50% of balance)

## Files Modified

### Database Migration
- `database/migrations/2025_08_27_151500_add_rd_account_conditions_fields.php`
  - Added all new fields for the conditions

### Model
- `app/Models/RdAccount.php`
  - Updated `$fillable` array with new fields
  - Updated `$casts` array with new field types
  - Added new methods for business logic
  - Updated rebate and penalty calculations

### Controller
- `app/Http/Controllers/RDAccountController.php`
  - Updated validation rules in `store()` method
  - Added validation for new fields

### Views
- `resources/views/admin/rd-accounts/create.blade.php`
  - Added form fields for all new conditions
  - Added JavaScript for conditional field visibility
  - Updated validation messages

### Tests
- `tests/Feature/RDAccountConditionsTest.php`
  - Comprehensive test suite covering all new functionality

## Validation Rules

The following validation rules are now enforced:

1. **Monthly Amount**: Minimum Rs. 100, multiples of Rs. 10
2. **Payment Method**: Required, must be 'cash' or 'cheque'
3. **Cheque Details**: Required if payment method is 'cheque'
4. **Joint Account**: Additional joint holders optional
5. **Phone Numbers**: 10-digit format validation

## Business Logic Methods

New methods added to `RdAccount` model:

- `validateMonthlyAmount()` - Validates amount meets requirements
- `isEligibleForPrematureClosure()` - Checks if 3+ years have passed
- `isEligibleForLoan()` - Checks if 12+ installments and 1+ year
- `getMaxLoanAmountAttribute()` - Calculates 50% of balance
- `getAllJointHoldersAttribute()` - Returns all account holders
- `hasNomination()` - Checks if nomination exists
- `isTransferred()` - Checks if account was transferred
- `hasLoan()` - Checks if loan was availed

## Testing

Comprehensive test suite covers:
- Monthly amount validation
- Rebate calculation
- Penalty calculation
- Premature closure eligibility
- Loan eligibility and calculation
- Joint holder management

## Usage

The system now fully supports all RD account conditions as specified. Users can:
- Create accounts with proper validation
- Manage joint accounts with up to 3 holders
- Track payment methods (cash/cheque)
- Set up nomination facilities
- Calculate rebates according to new scheme
- Check eligibility for premature closure and loans
- Transfer accounts between post offices
